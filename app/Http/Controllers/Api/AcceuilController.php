<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Acceuil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcceuilController extends Controller
{
    /**
     * Get the current Acceuil content.
     */
    public function index(): JsonResponse
    {
        $acceuil = Acceuil::getSingleton();

        $data = $acceuil->toArray();
        if (!empty($data['video_url']) && str_starts_with($data['video_url'], '/storage/')) {
            $data['video_url'] = url($data['video_url']);
        }
        if (!empty($data['arch_image']) && str_starts_with($data['arch_image'], '/storage/')) {
            $data['arch_image'] = url($data['arch_image']);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Update the Acceuil content.
     */
    public function update(Request $request): JsonResponse
    {
        $acceuil = Acceuil::getSingleton();

        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title_line1' => ['nullable', 'string', 'max:255'],
            'title_line2' => ['nullable', 'string', 'max:255'],
            'title_line3' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string'],
            'arch_badge' => ['nullable', 'string', 'max:100'],
            'arch_title' => ['nullable', 'string', 'max:255'],
            'arch_material' => ['nullable', 'string', 'max:100'],
            'arch_image' => ['nullable', 'string'],
            'stat1_number' => ['nullable', 'string', 'max:50'],
            'stat1_label' => ['nullable', 'string', 'max:100'],
            'stat2_number' => ['nullable', 'string', 'max:50'],
            'stat2_label' => ['nullable', 'string', 'max:100'],
            'stat3_number' => ['nullable', 'string', 'max:50'],
            'stat3_label' => ['nullable', 'string', 'max:100'],
            'marquee_words' => ['nullable'],
        ]);

        // Handle uploaded video file if present
        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('videos', 'public');
            $data['video_url'] = url(Storage::url($path));
        }

        // Handle uploaded arch image file if present
        if ($request->hasFile('arch_image_file')) {
            $path = $request->file('arch_image_file')->store('images/hero', 'public');
            $data['arch_image'] = url(Storage::url($path));
        }

        // If marquee_words is sent as JSON string or comma-separated string
        if (isset($data['marquee_words']) && is_string($data['marquee_words'])) {
            $decoded = json_decode($data['marquee_words'], true);
            if (is_array($decoded)) {
                $data['marquee_words'] = $decoded;
            } else {
                $data['marquee_words'] = array_map('trim', explode(',', $data['marquee_words']));
            }
        }

        $acceuil->update($data);

        $fresh = $acceuil->fresh()->toArray();
        if (!empty($fresh['video_url']) && str_starts_with($fresh['video_url'], '/storage/')) {
            $fresh['video_url'] = url($fresh['video_url']);
        }
        if (!empty($fresh['arch_image']) && str_starts_with($fresh['arch_image'], '/storage/')) {
            $fresh['arch_image'] = url($fresh['arch_image']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Contenu de la page d\'accueil mis à jour avec succès.',
            'data' => $fresh,
        ]);
    }

    /**
     * Reset Acceuil content to initial defaults.
     */
    public function reset(): JsonResponse
    {
        $acceuil = Acceuil::getSingleton();

        $defaults = [
            'eyebrow' => 'Marbre · Granit · Pierre naturelle — Maroc',
            'title_line1' => 'La pierre,',
            'title_line2' => 'taillée',
            'title_line3' => 'pour durer.',
            'description' => 'SUTRAVOMAR STONE LUXE sélectionne, façonne et pose des pierres nobles pour vos façades, escaliers, cuisines, piscines et espaces extérieurs. Du bloc brut à la finition parfaite.',
            'video_url' => '/videos/logo-reveal.mp4',
            'arch_badge' => 'Collection',
            'arch_title' => 'Beige de Taza, poli',
            'arch_material' => 'travertin',
            'arch_image' => null,
            'stat1_number' => '30+',
            'stat1_label' => 'Variétés de pierres',
            'stat2_number' => '100%',
            'stat2_label' => 'Sur mesure',
            'stat3_number' => 'Maroc',
            'stat3_label' => 'Livraison & pose',
            'marquee_words' => [
                'Pierre de Taza',
                'Marbre',
                'Granit',
                'Travertin',
                'Ardoise',
                'Sur mesure',
                'Pose & livraison',
            ],
        ];

        $acceuil->update($defaults);

        return response()->json([
            'status' => 'success',
            'message' => 'Contenu réinitialisé aux valeurs par défaut.',
            'data' => $acceuil->fresh(),
        ]);
    }
}
