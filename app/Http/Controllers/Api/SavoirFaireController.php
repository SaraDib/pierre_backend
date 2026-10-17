<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavoirFaireItem;
use App\Models\SavoirFaireSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SavoirFaireController extends Controller
{
    private function absoluteUrl(?string $path): ?string
    {
        if (!empty($path) && str_starts_with($path, '/storage/')) {
            return url($path);
        }
        return $path;
    }

    private function payload(bool $onlyActive): array
    {
        $items = SavoirFaireItem::query()
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                $arr = $item->toArray();
                $arr['image'] = $this->absoluteUrl($arr['image']);
                return $arr;
            })
            ->values();

        return [
            'section' => SavoirFaireSection::getSingleton()->toArray(),
            'items' => $items,
        ];
    }

    /** Public: visible content only. */
    public function index(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $this->payload(true)]);
    }

    /** Admin: all content, including hidden items. */
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

        $section = SavoirFaireSection::getSingleton();
        $section->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'En-tête de la section mis à jour avec succès.',
            'data' => $section->fresh(),
        ]);
    }

    /** Admin: create item. */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateItem($request);

        if ($request->hasFile('image_file')) {
            $data['image'] = Storage::url($request->file('image_file')->store('images/savoir-faire', 'public'));
        }
        unset($data['image_file']);

        $data['position'] = (int) SavoirFaireItem::max('position') + 1;
        $item = SavoirFaireItem::create($data)->fresh();

        return response()->json(['status' => 'success', 'message' => 'Expertise ajoutée.', 'data' => $this->serialize($item)], 201);
    }

    /** Admin: update item (POST for multipart support). */
    public function update(Request $request, SavoirFaireItem $item): JsonResponse
    {
        $data = $this->validateItem($request);

        if ($request->hasFile('image_file')) {
            $this->deleteImage($item->image);
            $data['image'] = Storage::url($request->file('image_file')->store('images/savoir-faire', 'public'));
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($item->image);
            $data['image'] = null;
        }
        unset($data['image_file']);

        $item->update($data);

        return response()->json(['status' => 'success', 'message' => 'Expertise mise à jour.', 'data' => $this->serialize($item->fresh())]);
    }

    /** Admin: delete item. */
    public function destroy(SavoirFaireItem $item): JsonResponse
    {
        $this->deleteImage($item->image);
        $item->delete();

        return response()->json(['status' => 'success', 'message' => 'Expertise supprimée.']);
    }

    /** Admin: reorder items. Body: { ids: [3,1,2,...] } */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:savoir_faire_items,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            SavoirFaireItem::where('id', $id)->update(['position' => $index + 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Ordre mis à jour.', 'data' => $this->payload(false)['items']]);
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'mat_key' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'image_file' => ['nullable', 'image', 'max:5120'],
        ]);
    }

    private function serialize(SavoirFaireItem $item): array
    {
        $arr = $item->toArray();
        $arr['image'] = $this->absoluteUrl($arr['image'] ?? null);
        return $arr;
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
