<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PourquoiChoisirSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PourquoiChoisirController extends Controller
{
    private function absoluteUrl(?string $path): ?string
    {
        if (! empty($path) && str_starts_with($path, '/storage/')) {
            return url($path);
        }

        return $path;
    }

    private function serialize(PourquoiChoisirSection $section): array
    {
        $arr = $section->toArray();
        $arr['image'] = $this->absoluteUrl($arr['image'] ?? null);
        if (! is_array($arr['categories'] ?? null)) {
            $arr['categories'] = PourquoiChoisirSection::defaultCategories();
        }

        return $arr;
    }

    /** Public: get Pourquoi nous choisir section. */
    public function index(): JsonResponse
    {
        $section = PourquoiChoisirSection::getSingleton();

        return response()->json([
            'status' => 'success',
            'data' => $this->serialize($section),
        ]);
    }

    /** Admin: get Pourquoi nous choisir section. */
    public function adminIndex(): JsonResponse
    {
        $section = PourquoiChoisirSection::getSingleton();

        return response()->json([
            'status' => 'success',
            'data' => $this->serialize($section),
        ]);
    }

    /** Admin: update Pourquoi nous choisir section. */
    public function update(Request $request): JsonResponse
    {
        $section = PourquoiChoisirSection::getSingleton();

        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title_line1' => ['nullable', 'string', 'max:255'],
            'title_line2' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stat1_number' => ['nullable', 'string', 'max:100'],
            'stat1_label' => ['nullable', 'string', 'max:255'],
            'stat2_number' => ['nullable', 'string', 'max:100'],
            'stat2_label' => ['nullable', 'string', 'max:255'],
            'stat3_number' => ['nullable', 'string', 'max:100'],
            'stat3_label' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_alt' => ['nullable', 'string', 'max:500'],
            'quote_line1' => ['nullable', 'string', 'max:255'],
            'quote_line2' => ['nullable', 'string', 'max:255'],
            'quote_line3' => ['nullable', 'string', 'max:255'],
            'categories' => ['nullable'],
            'image_file' => ['nullable', 'image', 'max:8192'],
        ]);

        if ($request->hasFile('image_file')) {
            $this->deleteOldImage($section->image);
            $data['image'] = Storage::url($request->file('image_file')->store('images/pourquoi-choisir', 'public'));
        }
        unset($data['image_file']);

        if (isset($data['categories'])) {
            $cats = $data['categories'];
            if (is_string($cats)) {
                $decoded = json_decode($cats, true);
                $data['categories'] = is_array($decoded) ? $decoded : PourquoiChoisirSection::defaultCategories();
            }
        }

        $section->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Section « Pourquoi nous choisir ? » mise à jour avec succès.',
            'data' => $this->serialize($section->fresh()),
        ]);
    }

    /** Admin: reset Pourquoi nous choisir section to defaults. */
    public function reset(): JsonResponse
    {
        $section = PourquoiChoisirSection::getSingleton();
        $this->deleteOldImage($section->image);
        $section->update(PourquoiChoisirSection::defaultAttributes());

        return response()->json([
            'status' => 'success',
            'message' => 'Section « Pourquoi nous choisir ? » réinitialisée aux valeurs par défaut.',
            'data' => $this->serialize($section->fresh()),
        ]);
    }

    private function deleteOldImage(?string $url): void
    {
        if (! $url) {
            return;
        }
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if (str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('/storage/')));
        }
    }
}

