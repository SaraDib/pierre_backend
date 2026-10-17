<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use App\Models\FaqItem;
use App\Models\FaqSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    private function serializeItem(FaqItem $item): array
    {
        $category = $item->category;

        return [
            'id' => $item->id,
            'question' => $item->question,
            'answer' => $item->answer,
            'category_id' => $item->category_id,
            'category_slug' => $category?->slug,
            'category_name' => $category?->name,
            'position' => (int) $item->position,
            'is_featured' => (bool) $item->is_featured,
            'is_active' => (bool) $item->is_active,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),

            // Legacy / convenience aliases for templates
            'q' => $item->question,
            'a' => $item->answer,
        ];
    }

    private function payload(bool $onlyActive, ?string $categorySlug = null, ?bool $featured = null, ?string $search = null): array
    {
        $categories = FaqCategory::query()
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $items = FaqItem::query()
            ->with('category')
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->when($categorySlug, function ($q) use ($categorySlug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug));
            })
            ->when($featured !== null, fn ($q) => $q->where('is_featured', $featured))
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%");
                });
            })
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn ($item) => $this->serializeItem($item))
            ->values();

        return [
            'section' => FaqSection::getSingleton()->toArray(),
            'categories' => $categories,
            'items' => $items,
        ];
    }

    /**
     * Public: visible questions, categories, and section header
     */
    public function index(Request $request): JsonResponse
    {
        $categorySlug = $request->query('category');
        $featured = $request->has('featured') ? $request->boolean('featured') : null;
        $search = $request->query('q');

        return response()->json([
            'status' => 'success',
            'data' => $this->payload(true, $categorySlug, $featured, $search),
        ]);
    }

    /**
     * Admin: all questions (including hidden), categories, and section header
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $categorySlug = $request->query('category');
        $search = $request->query('q');

        return response()->json([
            'status' => 'success',
            'data' => $this->payload(false, $categorySlug, null, $search),
        ]);
    }

    /* ------------------------------------------------------------- Section */

    public function updateSection(Request $request): JsonResponse
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'title_em' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cta_text' => ['nullable', 'string', 'max:255'],
            'cta_link' => ['nullable', 'string', 'max:500'],
        ]);

        $section = FaqSection::getSingleton();
        $section->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'En-tête de la FAQ mis à jour avec succès.',
            'data' => $section->fresh(),
        ]);
    }

    /* --------------------------------------------------------------- Items */

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateItem($request, false);

        if (!isset($data['position']) || $data['position'] === null) {
            $data['position'] = (int) FaqItem::max('position') + 1;
        }

        $item = FaqItem::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Question ajoutée avec succès.',
            'data' => $this->serializeItem($item->fresh(['category'])),
        ], 201);
    }

    public function update(Request $request, FaqItem $item): JsonResponse
    {
        $data = $this->validateItem($request, true);

        $item->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Question mise à jour avec succès.',
            'data' => $this->serializeItem($item->fresh(['category'])),
        ]);
    }

    public function destroy(FaqItem $item): JsonResponse
    {
        $item->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Question supprimée avec succès.',
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:faq_items,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            FaqItem::where('id', $id)->update([
                'position' => $index + 1,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ordre des questions mis à jour.',
            'data' => $this->payload(false)['items'],
        ]);
    }

    /* ---------------------------------------------------------- Categories */

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $this->validateCategory($request);
        $data['slug'] = $this->uniqueCategorySlug($data['name']);
        $data['position'] = (int) FaqCategory::max('position') + 1;

        $category = FaqCategory::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Catégorie créée avec succès.',
            'data' => $category->fresh(),
        ], 201);
    }

    public function updateCategory(Request $request, FaqCategory $category): JsonResponse
    {
        $data = $this->validateCategory($request, true);
        $category->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Catégorie mise à jour avec succès.',
            'data' => $category->fresh(),
        ]);
    }

    public function destroyCategory(FaqCategory $category): JsonResponse
    {
        // Attached questions become uncategorized (category_id becomes null)
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Catégorie supprimée avec succès.',
        ]);
    }

    public function reorderCategories(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:faq_categories,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            FaqCategory::where('id', $id)->update([
                'position' => $index + 1,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ordre des catégories mis à jour.',
            'data' => FaqCategory::orderBy('position')->orderBy('id')->get(),
        ]);
    }

    /* ------------------------------------------------------------- Helpers */

    private function validateItem(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'question' => [$isUpdate ? 'sometimes' : 'required', 'string'],
            'answer' => [$isUpdate ? 'sometimes' : 'required', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:faq_categories,id'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateCategory(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name) ?: 'categorie';
        $slug = $base;
        $i = 2;
        while (FaqCategory::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
