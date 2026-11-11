<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatalogCategory;
use App\Models\CatalogMaterial;
use App\Models\MateriauxSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Types de matériaux (niveau 1 du catalogue) :
 *   GET /catalogue-categories          → types visibles (+ nombre de variétés)
 *   GET /catalogue-categories/{slug}   → un type + ses variétés visibles
 */
class CatalogCategoryController extends Controller
{
    private function absoluteUrl(?string $path): ?string
    {
        if (! empty($path) && str_starts_with($path, '/storage/')) {
            return url($path);
        }

        return $path;
    }

    /** Sérialise un type ; l'image retombe sur celle de la 1re variété si absente. */
    public function serialize(CatalogCategory $c, bool $onlyActive = true): array
    {
        $materials = $c->materials->when($onlyActive, fn ($col) => $col->where('is_active', true))->values();
        $count = $materials->count();
        $cover = $materials->first();

        return [
            'id' => $c->id,
            'slug' => $c->slug,
            'position' => $c->position,
            'name' => $c->name,
            'subtitle' => $c->subtitle,
            'display_subtitle' => $c->subtitle ?: ($count . ' ' . ($count > 1 ? 'variétés' : 'variété')),
            'description' => $c->description,
            'image' => $this->absoluteUrl($c->image ?: $cover?->image),
            'hero' => $this->absoluteUrl($c->hero_image ?: $c->image ?: $cover?->hero_image ?: $cover?->image),
            'raw_image' => $this->absoluteUrl($c->image),
            'raw_hero' => $this->absoluteUrl($c->hero_image),
            'fallbackGradient' => $c->fallback_gradient ?: $cover?->fallback_gradient,
            'materials_count' => $count,
            'is_active' => $c->is_active,
        ];
    }

    private function query(bool $onlyActive)
    {
        return CatalogCategory::query()
            ->with('materials')
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('position')
            ->orderBy('id');
    }

    private function sectionData(): array
    {
        $section = MateriauxSection::getSingleton();

        return [
            'eyebrow' => $section->eyebrow ?: 'NOS MATÉRIAUX',
            'title' => $section->title ?: "Une sélection d'exception",
            'title_em' => $section->title_em ?: '',
            'description' => $section->description ?: 'Marbre, granit, pierre naturelle... Des matériaux nobles, soigneusement sélectionnés pour leur beauté, leur résistance et leur durabilité.',
        ];
    }

    /** Public : types visibles ayant au moins une variété visible. */
    public function index(): JsonResponse
    {
        $items = $this->query(true)->get()
            ->map(fn ($c) => $this->serialize($c, true))
            ->filter(fn ($c) => $c['materials_count'] > 0)
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'section' => $this->sectionData(),
        ]);
    }

    /** Public : un type + ses variétés visibles + type précédent/suivant. */
    public function show(string $slug, CatalogMaterialController $materials): JsonResponse
    {
        $all = $this->query(true)->get()->filter(fn ($c) => $c->materials->where('is_active', true)->count() > 0)->values();
        $index = $all->search(fn ($c) => $c->slug === $slug);

        if ($index === false) {
            return response()->json(['status' => 'error', 'message' => 'Type de matériau introuvable.'], 404);
        }

        $category = $all[$index];
        $count = $all->count();
        $prev = $count > 1 ? $all[($index - 1 + $count) % $count] : null;
        $next = $count > 1 ? $all[($index + 1) % $count] : null;

        $varieties = $category->materials
            ->where('is_active', true)
            ->values()
            ->map(function ($m) use ($category, $materials) {
                $m->setRelation('category', $category);

                return $materials->serialize($m);
            });

        return response()->json([
            'status' => 'success',
            'data' => $this->serialize($category, true),
            'materials' => $varieties,
            'prev' => $prev ? ['slug' => $prev->slug, 'name' => $prev->name] : null,
            'next' => $next ? ['slug' => $next->slug, 'name' => $next->name] : null,
        ]);
    }

    /** Admin : tous les types (y compris masqués / vides). */
    public function adminIndex(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->query(false)->get()->map(fn ($c) => $this->serialize($c, false))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['position'] = (int) CatalogCategory::max('position') + 1;
        $data['image'] = $request->hasFile('image_file') ? $this->storeFile($request->file('image_file')) : null;
        $data['hero_image'] = $request->hasFile('hero_file') ? $this->storeFile($request->file('hero_file')) : null;

        $category = CatalogCategory::create($this->columns($data));

        return response()->json([
            'status' => 'success',
            'message' => 'Type de matériau ajouté.',
            'data' => $this->serialize($category->fresh('materials'), false),
        ], 201);
    }

    /** Admin : mise à jour (POST pour le multipart). Le slug reste stable. */
    public function update(Request $request, CatalogCategory $category): JsonResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image_file')) {
            $this->deleteFile($category->image);
            $data['image'] = $this->storeFile($request->file('image_file'));
        } elseif ($request->boolean('remove_image')) {
            $this->deleteFile($category->image);
            $data['image'] = null;
        }

        if ($request->hasFile('hero_file')) {
            $this->deleteFile($category->hero_image);
            $data['hero_image'] = $this->storeFile($request->file('hero_file'));
        } elseif ($request->boolean('remove_hero')) {
            $this->deleteFile($category->hero_image);
            $data['hero_image'] = null;
        }

        $category->update($this->columns($data));

        // Garde la colonne `type` des variétés synchronisée avec le nom du type.
        CatalogMaterial::where('category_id', $category->id)->update(['type' => $category->name]);

        return response()->json([
            'status' => 'success',
            'message' => 'Type de matériau mis à jour.',
            'data' => $this->serialize($category->fresh('materials'), false),
        ]);
    }

    /** Admin : suppression refusée tant que des variétés y sont rattachées. */
    public function destroy(CatalogCategory $category): JsonResponse
    {
        if ($category->materials()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ce type contient encore des variétés : supprimez-les ou déplacez-les vers un autre type avant de le supprimer.',
            ], 422);
        }

        $this->deleteFile($category->image);
        $this->deleteFile($category->hero_image);
        $category->delete();

        return response()->json(['status' => 'success', 'message' => 'Type de matériau supprimé.']);
    }

    /** Body : { ids: [3,1,2,...] } */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:catalog_categories,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            CatalogCategory::where('id', $id)->update(['position' => $index + 1]);
        }

        return $this->adminIndex();
    }

    /* -------------------------------------------------------------- Helpers */

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'fallback_gradient' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'hero_file' => ['nullable', 'image', 'max:5120'],
        ]);
    }

    private function columns(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'slug', 'position', 'name', 'subtitle', 'description', 'image', 'hero_image', 'fallback_gradient', 'is_active',
        ]));
    }

    private function storeFile($file): string
    {
        return Storage::url($file->store('images/catalogue', 'public'));
    }

    private function deleteFile(?string $url): void
    {
        if (! $url) {
            return;
        }
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if (str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('/storage/')));
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'type';
        $slug = $base;
        $i = 2;
        while (CatalogCategory::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}

