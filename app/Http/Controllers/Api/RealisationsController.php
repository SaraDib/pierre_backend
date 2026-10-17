<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MateriauxItem;
use App\Models\RealisationsCategory;
use App\Models\RealisationsImage;
use App\Models\RealisationsItem;
use App\Models\RealisationsSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RealisationsController extends Controller
{
    private function absoluteUrl(?string $path): ?string
    {
        if (!empty($path) && str_starts_with($path, '/storage/')) {
            return url($path);
        }
        return $path;
    }

    private function deleteImage(?string $path): void
    {
        if (empty($path) || !str_starts_with($path, '/storage/')) {
            return;
        }
        $relative = str_replace('/storage/', '', $path);
        Storage::disk('public')->delete($relative);
    }

    private function uniqueSlug(string $modelClass, ?string $source, string $fallback, ?int $ignoreId = null): string
    {
        $base = Str::slug($source ?: $fallback);
        $slug = $base ?: $fallback;
        $count = 1;

        while ($modelClass::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-" . (++$count);
        }

        return $slug;
    }

    private function serializeItem(RealisationsItem $item): array
    {
        $category = $item->category;
        $coverUrl = $this->absoluteUrl($item->cover_image);

        $images = $item->images->map(function (RealisationsImage $img) {
            return [
                'id' => $img->id,
                'image_path' => $this->absoluteUrl($img->image_path),
                'caption' => $img->caption,
                'position' => $img->position,
            ];
        })->values()->toArray();

        $materiaux = $item->materiaux->map(function (MateriauxItem $m) {
            return [
                'id' => $m->id,
                'slug' => $m->slug,
                'name' => $m->name,
            ];
        })->values()->toArray();

        $catLabel = $category?->badge_label ?: ($category?->name ?: 'Projet sur mesure');

        return [
            'id' => $item->id,
            'slug' => $item->slug,
            'category_id' => $item->category_id,
            'category_slug' => $category?->slug,
            'category_name' => $category?->name,
            'category_badge' => $category?->badge_label,
            'position' => $item->position,
            'title' => $item->title,
            'subtitle' => $item->subtitle,
            'short_title' => $item->short_title,
            'short_description' => $item->short_description,
            'description' => $item->description,
            'stone' => $item->stone,
            'finish' => $item->finish,
            'location' => $item->location,
            'cover_image' => $coverUrl,
            'grid_class' => $item->grid_class,
            'is_featured' => (bool) $item->is_featured,
            'is_active' => (bool) $item->is_active,
            'images' => $images,
            'materiaux' => $materiaux,
            'materiaux_ids' => $item->materiaux->pluck('id')->values()->toArray(),

            // Legacy field aliases for 100% frontend drop-in compatibility
            'category' => $category?->slug ?? 'all',
            'catLabel' => $catLabel,
            'shortTitle' => $item->short_title ?: $item->title,
            'shortDesc' => $item->short_description ?: $item->description,
            'desc' => $item->description ?: $item->short_description,
            'loc' => $item->location,
            'img' => $coverUrl,
            'gridClass' => $item->grid_class,
        ];
    }

    private function payload(bool $onlyActive, ?string $categorySlug = null, bool $featuredOnly = false): array
    {
        $itemsQuery = RealisationsItem::query()
            ->with(['category', 'images', 'materiaux'])
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->when($featuredOnly, fn ($q) => $q->where('is_featured', true))
            ->when($categorySlug && $categorySlug !== 'all', function ($q) use ($categorySlug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug));
            })
            ->orderBy('position')
            ->orderBy('id');

        $items = $itemsQuery->get()->map(fn ($item) => $this->serializeItem($item))->values();

        $categories = RealisationsCategory::query()
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return [
            'section' => RealisationsSection::getSingleton()->toArray(),
            'categories' => $categories,
            'items' => $items,
        ];
    }

    /** Public: visible projects, optional ?category=slug or ?featured=1 */
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $featured = $request->boolean('featured');

        return response()->json([
            'status' => 'success',
            'data' => $this->payload(true, is_string($category) && $category !== '' ? $category : null, $featured),
        ]);
    }

    /** Admin: everything, including hidden items, plus materiaux list for selector */
    public function adminIndex(): JsonResponse
    {
        $data = $this->payload(false);
        $data['materiaux_options'] = MateriauxItem::query()
            ->orderBy('position')
            ->orderBy('id')
            ->get(['id', 'slug', 'name'])
            ->toArray();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /** Admin: update section headers (hero, bento, cta) */
    public function updateSection(Request $request): JsonResponse
    {
        $data = $request->validate([
            'hero_eyebrow' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_title_em' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string'],

            'bento_eyebrow' => ['nullable', 'string', 'max:255'],
            'bento_title' => ['nullable', 'string', 'max:255'],
            'bento_title_em' => ['nullable', 'string', 'max:255'],
            'bento_description' => ['nullable', 'string'],

            'cta_title' => ['nullable', 'string', 'max:255'],
            'cta_title_em' => ['nullable', 'string', 'max:255'],
            'cta_description' => ['nullable', 'string'],
        ]);

        $section = RealisationsSection::getSingleton();
        $section->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'En-têtes de Réalisations mis à jour.',
            'data' => $section->fresh(),
        ]);
    }

    /* ---------------------------------------------------------------- Items */

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateItem($request);

        if ($request->hasFile('cover_image_file')) {
            $data['cover_image'] = Storage::url($request->file('cover_image_file')->store('images/realisations', 'public'));
        }
        unset($data['cover_image_file']);

        $materiauxIds = $this->extractMateriauxIds($request);
        unset($data['materiaux_ids']);

        $data['slug'] = $this->uniqueSlug(RealisationsItem::class, $data['title'], 'realisation');
        $data['position'] = (int) RealisationsItem::max('position') + 1;

        $item = DB::transaction(function () use ($data, $materiauxIds, $request) {
            $created = RealisationsItem::create($data);

            if (!empty($materiauxIds)) {
                $created->materiaux()->sync($materiauxIds);
            }

            // Secondary gallery images
            if ($request->hasFile('gallery_files')) {
                foreach ($request->file('gallery_files') as $gIdx => $gFile) {
                    $gPath = Storage::url($gFile->store('images/realisations/gallery', 'public'));
                    RealisationsImage::create([
                        'realisation_item_id' => $created->id,
                        'image_path' => $gPath,
                        'position' => $gIdx + 1,
                    ]);
                }
            }

            return $created;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Projet ajouté avec succès.',
            'data' => $this->serializeItem($item->fresh(['category', 'images', 'materiaux'])),
        ], 201);
    }

    public function update(Request $request, RealisationsItem $item): JsonResponse
    {
        $data = $this->validateItem($request, $item->id);

        if ($request->hasFile('cover_image_file')) {
            $this->deleteImage($item->cover_image);
            $data['cover_image'] = Storage::url($request->file('cover_image_file')->store('images/realisations', 'public'));
        } elseif ($request->boolean('remove_cover_image')) {
            $this->deleteImage($item->cover_image);
            $data['cover_image'] = null;
        }
        unset($data['cover_image_file'], $data['remove_cover_image']);

        $materiauxIds = $this->extractMateriauxIds($request);
        unset($data['materiaux_ids']);

        DB::transaction(function () use ($item, $data, $materiauxIds, $request) {
            $item->update($data);

            if ($materiauxIds !== null) {
                $item->materiaux()->sync($materiauxIds);
            }

            // Remove selected secondary gallery images
            $removeGalleryIds = $request->input('remove_gallery_ids');
            if (is_string($removeGalleryIds)) {
                $removeGalleryIds = json_decode($removeGalleryIds, true) ?: [];
            }
            if (is_array($removeGalleryIds) && count($removeGalleryIds) > 0) {
                $imagesToRemove = RealisationsImage::where('realisation_item_id', $item->id)
                    ->whereIn('id', $removeGalleryIds)
                    ->get();
                foreach ($imagesToRemove as $img) {
                    $this->deleteImage($img->image_path);
                    $img->delete();
                }
            }

            // Add new gallery images
            if ($request->hasFile('gallery_files')) {
                $curMax = (int) RealisationsImage::where('realisation_item_id', $item->id)->max('position');
                foreach ($request->file('gallery_files') as $gIdx => $gFile) {
                    $gPath = Storage::url($gFile->store('images/realisations/gallery', 'public'));
                    RealisationsImage::create([
                        'realisation_item_id' => $item->id,
                        'image_path' => $gPath,
                        'position' => $curMax + $gIdx + 1,
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Projet mis à jour avec succès.',
            'data' => $this->serializeItem($item->fresh(['category', 'images', 'materiaux'])),
        ]);
    }

    public function destroy(RealisationsItem $item): JsonResponse
    {
        $this->deleteImage($item->cover_image);

        foreach ($item->images as $img) {
            $this->deleteImage($img->image_path);
        }

        $item->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Projet supprimé avec succès.',
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:realisations_items,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            RealisationsItem::where('id', $id)->update(['position' => $index + 1]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ordre des projets mis à jour.',
            'data' => $this->payload(false)['items'],
        ]);
    }

    /* ----------------------------------------------------------- Categories */

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $this->validateCategory($request);
        $data['slug'] = $this->uniqueSlug(RealisationsCategory::class, $data['name'], 'categorie');
        $data['position'] = (int) RealisationsCategory::max('position') + 1;
        $category = RealisationsCategory::create($data)->fresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Catégorie ajoutée.',
            'data' => $category,
        ], 201);
    }

    public function updateCategory(Request $request, RealisationsCategory $category): JsonResponse
    {
        $category->update($this->validateCategory($request));

        return response()->json([
            'status' => 'success',
            'message' => 'Catégorie mise à jour.',
            'data' => $category->fresh(),
        ]);
    }

    public function destroyCategory(RealisationsCategory $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Catégorie supprimée.',
        ]);
    }

    public function reorderCategories(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:realisations_categories,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            RealisationsCategory::where('id', $id)->update(['position' => $index + 1]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ordre des catégories mis à jour.',
            'data' => $this->payload(false)['categories'],
        ]);
    }

    /* -------------------------------------------------------------- Helpers */

    private function extractMateriauxIds(Request $request): ?array
    {
        $input = $request->input('materiaux_ids');
        if ($input === null) {
            return null;
        }
        if (is_string($input)) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                return array_map('intval', $decoded);
            }
            return array_filter(array_map('intval', explode(',', $input)));
        }
        if (is_array($input)) {
            return array_map('intval', $input);
        }
        return [];
    }

    private function validateItem(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:realisations_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'short_title' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'stone' => ['nullable', 'string', 'max:255'],
            'finish' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'string', 'max:1024'],
            'cover_image_file' => ['nullable', 'image', 'max:8192'],
            'grid_class' => ['nullable', 'string', 'max:20'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'materiaux_ids' => ['nullable'],
            'gallery_files.*' => ['nullable', 'image', 'max:8192'],
            'remove_gallery_ids' => ['nullable'],
            'remove_cover_image' => ['nullable', 'boolean'],
        ]);
    }

    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'badge_label' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
