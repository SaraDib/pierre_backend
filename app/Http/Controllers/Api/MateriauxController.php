<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MateriauxCategory;
use App\Models\MateriauxItem;
use App\Models\MateriauxSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MateriauxController extends Controller
{
    private function absoluteUrl(?string $path): ?string
    {
        if (!empty($path) && str_starts_with($path, '/storage/')) {
            return url($path);
        }
        return $path;
    }

    private function serializeItem(MateriauxItem $item): array
    {
        $category = $item->category;

        return [
            'id' => $item->id,
            'slug' => $item->slug,
            'name' => $item->name,
            'description' => $item->description,
            'finishes' => $item->finishes ?? [],
            'category_id' => $item->category_id,
            'category_slug' => $category?->slug,
            'category_name' => $category?->name,
            'position' => $item->position,
            'is_active' => $item->is_active,
            'image' => $this->absoluteUrl($item->image),
            'texture' => $this->textureOf($item),
        ];
    }

    private function textureOf(MateriauxItem $item): array
    {
        return [
            'type' => $item->texture_type,
            'base' => $item->base,
            'vein' => $item->vein,
            'tone' => $item->tone,
            'freq' => $item->freq,
            'seed' => $item->seed,
        ];
    }

    private function payload(bool $onlyActive, ?string $categorySlug = null): array
    {
        $items = MateriauxItem::query()
            ->with('category')
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->when($categorySlug, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn ($item) => $this->serializeItem($item))
            ->values();

        $categories = MateriauxCategory::query()
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $payload = [
            'section' => MateriauxSection::getSingleton()->toArray(),
            'categories' => $categories,
            'items' => $items,
        ];

        if ($onlyActive) {
            // Texture library for every material, visible or not. Other sections
            // (Accueil arch card, Savoir-faire layers) reference materials by slug.
            $payload['textures'] = MateriauxItem::query()
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->map(fn ($item) => ['slug' => $item->slug, 'name' => $item->name] + $this->textureOf($item))
                ->values();
        }

        return $payload;
    }

    /** Public: visible materials only, optional ?category=slug filter. */
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');

        return response()->json([
            'status' => 'success',
            'data' => $this->payload(true, is_string($category) && $category !== '' ? $category : null),
        ]);
    }

    /** Admin: everything, including hidden items and categories. */
    public function adminIndex(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $this->payload(false)]);
    }

    /** Admin: update section header. */
    public function updateSection(Request $request): JsonResponse
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'title_em' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $section = MateriauxSection::getSingleton();
        $section->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'En-tête de la section mis à jour avec succès.',
            'data' => $section->fresh(),
        ]);
    }

    /* ---------------------------------------------------------------- Items */

    /** Admin: create material. */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateItem($request);

        if ($request->hasFile('image_file')) {
            $data['image'] = Storage::url($request->file('image_file')->store('images/materiaux', 'public'));
        }
        unset($data['image_file']);

        $data['slug'] = $this->uniqueSlug(MateriauxItem::class, $data['name'], 'materiau');
        $data['position'] = (int) MateriauxItem::max('position') + 1;
        $item = MateriauxItem::create($data)->fresh('category');

        return response()->json(['status' => 'success', 'message' => 'Matériau ajouté.', 'data' => $this->serializeItem($item)], 201);
    }

    /** Admin: update material (POST for multipart support). The slug never changes. */
    public function update(Request $request, MateriauxItem $item): JsonResponse
    {
        $data = $this->validateItem($request);

        if ($request->hasFile('image_file')) {
            $this->deleteImage($item->image);
            $data['image'] = Storage::url($request->file('image_file')->store('images/materiaux', 'public'));
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($item->image);
            $data['image'] = null;
        }
        unset($data['image_file']);

        $item->update($data);

        return response()->json(['status' => 'success', 'message' => 'Matériau mis à jour.', 'data' => $this->serializeItem($item->fresh('category'))]);
    }

    /** Admin: delete material. References held by other sections are cleared. */
    public function destroy(MateriauxItem $item): JsonResponse
    {
        $this->deleteImage($item->image);

        DB::table('savoir_faire_items')->where('mat_key', $item->slug)->update(['mat_key' => null]);
        DB::table('acceuil')->where('arch_material', $item->slug)->update(['arch_material' => null]);

        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Matériau supprimé.']);
    }

    /** Admin: reorder materials. Body: { ids: [3,1,2,...] } */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:materiaux_items,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            MateriauxItem::where('id', $id)->update(['position' => $index + 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Ordre mis à jour.', 'data' => $this->payload(false)['items']]);
    }

    /* ----------------------------------------------------------- Categories */

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $this->validateCategory($request);
        $data['slug'] = $this->uniqueSlug(MateriauxCategory::class, $data['name'], 'categorie');
        $data['position'] = (int) MateriauxCategory::max('position') + 1;
        $category = MateriauxCategory::create($data)->fresh();

        return response()->json(['status' => 'success', 'message' => 'Catégorie ajoutée.', 'data' => $category], 201);
    }

    public function updateCategory(Request $request, MateriauxCategory $category): JsonResponse
    {
        $category->update($this->validateCategory($request));

        return response()->json(['status' => 'success', 'message' => 'Catégorie mise à jour.', 'data' => $category->fresh()]);
    }

    /** Materials of a deleted category are kept and simply become uncategorised. */
    public function destroyCategory(MateriauxCategory $category): JsonResponse
    {
        $category->delete();

        return response()->json(['status' => 'success', 'message' => 'Catégorie supprimée.']);
    }

    public function reorderCategories(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:materiaux_categories,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            MateriauxCategory::where('id', $id)->update(['position' => $index + 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Ordre mis à jour.', 'data' => $this->payload(false)['categories']]);
    }

    /* -------------------------------------------------------------- Helpers */

    private function validateItem(Request $request): array
    {
        // Multipart requests send `finishes` as a JSON string (same approach as Accueil `marquee_words`).
        $finishes = $request->input('finishes');
        if (is_string($finishes)) {
            $decoded = json_decode($finishes, true);
            $request->merge(['finishes' => is_array($decoded) ? $decoded : array_map('trim', explode(',', $finishes))]);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:materiaux_categories,id'],
            'finishes' => ['nullable', 'array'],
            'finishes.*' => ['string', 'max:100'],
            'texture_type' => ['sometimes', 'in:veins,granit,bands,grain'],
            'base' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'vein' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'tone' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'freq' => ['sometimes', 'max:30', 'regex:/^\d*\.?\d+(\s\d*\.?\d+)?$/'],
            'seed' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'image_file' => ['nullable', 'image', 'max:5120'],
        ]);
    }

    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $model, string $name, string $fallback): string
    {
        $base = Str::slug($name) ?: $fallback;
        $slug = $base;
        $i = 2;
        while ($model::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function deleteImage(?string $url): void
    {
        if (!$url) {
            return;
        }
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if (str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('/storage/')));
        }
    }
}
