<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MethodeItem;
use App\Models\MethodeSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MethodeController extends Controller
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

    private function serializeItem(MethodeItem $item): array
    {
        $stepNumber = $item->step_number ?: str_pad((string) $item->position, 2, '0', STR_PAD_LEFT);
        $imageUrl = $this->absoluteUrl($item->image);

        return [
            'id' => $item->id,
            'step_number' => $stepNumber,
            'title' => $item->title,
            'short_description' => $item->short_description,
            'description' => $item->description,
            'duration' => $item->duration,
            'icon' => $item->icon ?: 'Ruler',
            'image' => $imageUrl,
            'position' => $item->position,
            'is_active' => (bool) $item->is_active,

            // Legacy / convenience aliases
            'step' => $stepNumber,
            'desc' => $item->short_description ?: $item->description,
        ];
    }

    private function payload(bool $onlyActive): array
    {
        $items = MethodeItem::query()
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn ($item) => $this->serializeItem($item))
            ->values();

        return [
            'section' => MethodeSection::getSingleton()->toArray(),
            'items' => $items,
        ];
    }

    /** Public: visible steps and section headers */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->payload(true),
        ]);
    }

    /** Admin: all steps (including hidden) and section headers */
    public function adminIndex(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->payload(false),
        ]);
    }

    /** Admin: update section header */
    public function updateSection(Request $request): JsonResponse
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'title_em' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $section = MethodeSection::getSingleton();
        $section->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'En-tête de la section Méthode mis à jour.',
            'data' => $section->fresh(),
        ]);
    }

    /* ---------------------------------------------------------------- Items */

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateItem($request);

        if ($request->hasFile('image_file')) {
            $data['image'] = Storage::url($request->file('image_file')->store('images/methode', 'public'));
        }
        unset($data['image_file']);

        $nextPos = (int) MethodeItem::max('position') + 1;
        $data['position'] = $nextPos;

        if (empty($data['step_number'])) {
            $data['step_number'] = str_pad((string) $nextPos, 2, '0', STR_PAD_LEFT);
        }

        $item = MethodeItem::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Étape ajoutée avec succès.',
            'data' => $this->serializeItem($item),
        ], 201);
    }

    public function update(Request $request, MethodeItem $item): JsonResponse
    {
        $data = $this->validateItem($request);

        if ($request->hasFile('image_file')) {
            $this->deleteImage($item->image);
            $data['image'] = Storage::url($request->file('image_file')->store('images/methode', 'public'));
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($item->image);
            $data['image'] = null;
        }
        unset($data['image_file'], $data['remove_image']);

        $item->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Étape mise à jour avec succès.',
            'data' => $this->serializeItem($item->fresh()),
        ]);
    }

    public function destroy(MethodeItem $item): JsonResponse
    {
        $this->deleteImage($item->image);
        $item->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Étape supprimée avec succès.',
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:methode_items,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            $pos = $index + 1;
            MethodeItem::where('id', $id)->update([
                'position' => $pos,
                'step_number' => str_pad((string) $pos, 2, '0', STR_PAD_LEFT),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ordre des étapes mis à jour.',
            'data' => $this->payload(false)['items'],
        ]);
    }

    /* -------------------------------------------------------------- Helpers */

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'step_number' => ['nullable', 'string', 'max:20'],
            'duration' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'string', 'max:1024'],
            'image_file' => ['nullable', 'image', 'max:8192'],
            'remove_image' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
