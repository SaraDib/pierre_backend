<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatalogMaterial;
use App\Models\MateriauxSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Catalogue des matériaux (carrousel accueil, /detailsmateriaux, /detailsmateriaux/{slug}).
 */
class CatalogMaterialController extends Controller
{
    private const MAX_GALLERY = 8;

    private function absoluteUrl(?string $path): ?string
    {
        if (!empty($path) && str_starts_with($path, '/storage/')) {
            return url($path);
        }
        return $path;
    }

    private function serialize(CatalogMaterial $m): array
    {
        return [
            'id' => $m->id,
            'slug' => $m->slug,
            'position' => $m->position,
            'type' => $m->type,
            'name' => $m->name,
            'variant' => $m->variant,
            'description' => $m->description,
            'image' => $this->absoluteUrl($m->image),
            'hero' => $this->absoluteUrl($m->hero_image),
            'gallery' => collect($m->gallery ?? [])
                ->map(fn ($g) => ['src' => $this->absoluteUrl($g['src'] ?? null), 'alt' => $g['alt'] ?? ''])
                ->filter(fn ($g) => !empty($g['src']))
                ->values()
                ->all(),
            'specs' => [
                'origin' => $m->origin,
                'finish' => $m->finish,
                'thickness' => $m->thickness,
                'uses' => $m->uses ?? [],
                'maintenance' => $m->maintenance,
            ],
            'fallbackGradient' => $m->fallback_gradient,
            'is_active' => $m->is_active,
        ];
    }

    private function list(bool $onlyActive): array
    {
        return CatalogMaterial::query()
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => $this->serialize($m))
            ->values()
            ->all();
    }

    private function sectionData(): array
    {
        $section = MateriauxSection::getSingleton();
        return [
            'id' => $section->id,
            'eyebrow' => $section->eyebrow ?: 'NOS MATÉRIAUX',
            'title' => $section->title ?: "Une sélection d'exception",
            'title_em' => $section->title_em ?: '',
            'description' => $section->description ?: 'Marbre, granit, pierre naturelle... Des matériaux nobles, soigneusement sélectionnés pour leur beauté, leur résistance et leur durabilité.',
        ];
    }

    /** Public : matériaux visibles uniquement + en-tête de section. */
    public function index(): JsonResponse
    {
        $items = $this->list(true);
        return response()->json([
            'status' => 'success',
            'data' => $items,
            'section' => $this->sectionData(),
        ]);
    }

    /** Public : détail d'un matériau par son slug + matériau précédent/suivant. */
    public function show(string $slug): JsonResponse
    {
        $items = $this->list(true);
        $index = collect($items)->search(fn ($m) => $m['slug'] === $slug);

        if ($index === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Matériau introuvable.',
            ], 404);
        }

        $count = count($items);
        $prev = $count > 1 ? $items[($index - 1 + $count) % $count] : null;
        $next = $count > 1 ? $items[($index + 1) % $count] : null;

        return response()->json([
            'status' => 'success',
            'data' => $items[$index],
            'prev' => $prev ? ['slug' => $prev['slug'], 'name' => $prev['name'], 'variant' => $prev['variant']] : null,
            'next' => $next ? ['slug' => $next['slug'], 'name' => $next['name'], 'variant' => $next['variant']] : null,
            'section' => $this->sectionData(),
        ]);
    }

    /** Admin : tout, y compris les matériaux masqués + en-tête de section. */
    public function adminIndex(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->list(false),
            'section' => $this->sectionData(),
        ]);
    }

    /** Admin : mise à jour de l'en-tête de section (#materiaux et /detailsmateriaux). */
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
            'data' => $this->sectionData(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug(trim($data['name'] . ' ' . ($data['variant'] ?? '')));
        $data['position'] = (int) CatalogMaterial::max('position') + 1;
        $data['image'] = $request->hasFile('image_file') ? $this->storeFile($request->file('image_file')) : null;
        $data['hero_image'] = $request->hasFile('hero_file') ? $this->storeFile($request->file('hero_file')) : null;
        $data['gallery'] = $this->buildGallery($request, []);

        $material = CatalogMaterial::create($this->columns($data));

        return response()->json([
            'status' => 'success',
            'message' => 'Matériau ajouté.',
            'data' => $this->serialize($material->fresh()),
        ], 201);
    }

    /** Admin : mise à jour (POST pour le multipart). Le slug ne change jamais. */
    public function update(Request $request, CatalogMaterial $item): JsonResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image_file')) {
            $this->deleteFile($item->image);
            $data['image'] = $this->storeFile($request->file('image_file'));
        } elseif ($request->boolean('remove_image')) {
            $this->deleteFile($item->image);
            $data['image'] = null;
        }

        if ($request->hasFile('hero_file')) {
            $this->deleteFile($item->hero_image);
            $data['hero_image'] = $this->storeFile($request->file('hero_file'));
        } elseif ($request->boolean('remove_hero')) {
            $this->deleteFile($item->hero_image);
            $data['hero_image'] = null;
        }

        // La galerie n'est modifiée que si le client l'envoie (clé `gallery` ou nouveaux fichiers).
        if ($request->has('gallery') || $request->hasFile('gallery_files')) {
            $previous = $item->gallery ?? [];
            $data['gallery'] = $this->buildGallery($request, $previous);

            $kept = collect($data['gallery'])->pluck('src')->all();
            foreach ($previous as $old) {
                if (!in_array($old['src'] ?? null, $kept, true)) {
                    $this->deleteFile($old['src'] ?? null);
                }
            }
        }

        $item->update($this->columns($data));

        return response()->json([
            'status' => 'success',
            'message' => 'Matériau mis à jour.',
            'data' => $this->serialize($item->fresh()),
        ]);
    }

    public function destroy(CatalogMaterial $item): JsonResponse
    {
        $this->deleteFile($item->image);
        $this->deleteFile($item->hero_image);
        foreach ($item->gallery ?? [] as $g) {
            $this->deleteFile($g['src'] ?? null);
        }

        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Matériau supprimé.']);
    }

    /** Body : { ids: [3,1,2,...] } */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:catalog_materials,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            CatalogMaterial::where('id', $id)->update(['position' => $index + 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Ordre mis à jour.', 'data' => $this->list(false)]);
    }

    /* -------------------------------------------------------------- Helpers */

    /** Décode les champs JSON envoyés en multipart, puis valide. */
    private function validated(Request $request): array
    {
        foreach (['uses', 'gallery', 'gallery_alts'] as $key) {
            $value = $request->input($key);
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if ($key === 'uses' && !is_array($decoded)) {
                    $decoded = array_values(array_filter(array_map('trim', explode(',', $value))));
                }
                $request->merge([$key => is_array($decoded) ? $decoded : []]);
            }
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'variant' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'origin' => ['nullable', 'string', 'max:255'],
            'finish' => ['nullable', 'string', 'max:255'],
            'thickness' => ['nullable', 'string', 'max:255'],
            'maintenance' => ['nullable', 'string'],
            'fallback_gradient' => ['nullable', 'string', 'max:255'],
            'uses' => ['nullable', 'array'],
            'uses.*' => ['string', 'max:150'],
            'gallery' => ['nullable', 'array', 'max:' . self::MAX_GALLERY],
            'gallery.*.src' => ['required_with:gallery', 'string', 'max:500'],
            'gallery.*.alt' => ['nullable', 'string', 'max:255'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'max:5120'],
            'gallery_alts' => ['nullable', 'array'],
            'gallery_alts.*' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'hero_file' => ['nullable', 'image', 'max:5120'],
        ]);

        $data['type'] = trim($data['type'] ?? '') !== '' ? trim($data['type']) : $data['name'];

        return $data;
    }

    /** Ne garde que les colonnes de la table. */
    private function columns(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'slug', 'position', 'type', 'name', 'variant', 'description', 'image', 'hero_image',
            'gallery', 'origin', 'finish', 'thickness', 'uses', 'maintenance', 'fallback_gradient', 'is_active',
        ]));
    }

    /**
     * Galerie finale = entrées conservées (`gallery`) + nouveaux fichiers (`gallery_files`).
     * Les URLs absolues du backend sont ramenées à /storage/... avant enregistrement.
     */
    private function buildGallery(Request $request, array $previous): array
    {
        $gallery = [];
        foreach ((array) $request->input('gallery', []) as $g) {
            $src = $this->relativeUrl($g['src'] ?? '');
            if ($src !== '') {
                $gallery[] = ['src' => $src, 'alt' => trim($g['alt'] ?? '')];
            }
        }

        $alts = (array) $request->input('gallery_alts', []);
        foreach ((array) $request->file('gallery_files', []) as $i => $file) {
            $gallery[] = ['src' => $this->storeFile($file), 'alt' => trim($alts[$i] ?? '')];
        }

        return array_slice($gallery, 0, self::MAX_GALLERY);
    }

    private function relativeUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        return str_starts_with($path, '/storage/') ? $path : $url;
    }

    private function storeFile($file): string
    {
        return Storage::url($file->store('images/catalogue', 'public'));
    }

    private function deleteFile(?string $url): void
    {
        if (!$url) {
            return;
        }
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if (str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('/storage/')));
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'materiau';
        $slug = $base;
        $i = 2;
        while (CatalogMaterial::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
