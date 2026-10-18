<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * Format URLs for the client.
     */
    protected function formatUrls(array $data): array
    {
        if (!empty($data['logo_url']) && str_starts_with($data['logo_url'], '/storage/')) {
            $data['logo_url'] = url($data['logo_url']);
        }
        if (!empty($data['logo_footer_url']) && str_starts_with($data['logo_footer_url'], '/storage/')) {
            $data['logo_footer_url'] = url($data['logo_footer_url']);
        }
        return $data;
    }

    /**
     * Get the company information.
     */
    public function index(): JsonResponse
    {
        $setting = CompanySetting::getSingleton();

        return response()->json([
            'status' => 'success',
            'data' => $this->formatUrls($setting->toArray()),
        ]);
    }

    /**
     * Update the company information.
     */
    public function update(Request $request): JsonResponse
    {
        $setting = CompanySetting::getSingleton();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'logo_footer_url' => ['nullable', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:100'],
            'phone_secondary' => ['nullable', 'string', 'max:100'],
            'whatsapp_number' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'intervention_zone' => ['nullable', 'string', 'max:255'],
            'instagram_url' => ['nullable', 'string', 'max:500'],
            'facebook_url' => ['nullable', 'string', 'max:500'],
            'linkedin_url' => ['nullable', 'string', 'max:500'],
            'maps_url' => ['nullable', 'string'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'file', 'image', 'max:8192'],
            'logo_footer_file' => ['nullable', 'file', 'image', 'max:8192'],
        ]);

        // Handle uploaded logo file if present
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('images/company', 'public');
            $data['logo_url'] = url(Storage::url($path));
        }

        // Handle uploaded footer logo file if present
        if ($request->hasFile('logo_footer_file')) {
            $path = $request->file('logo_footer_file')->store('images/company', 'public');
            $data['logo_footer_url'] = url(Storage::url($path));
        }

        unset($data['logo_file'], $data['logo_footer_file']);

        $setting->update($data);

        $fresh = $this->formatUrls($setting->fresh()->toArray());

        return response()->json([
            'status' => 'success',
            'message' => 'Informations de l\'entreprise mises à jour avec succès.',
            'data' => $fresh,
        ]);
    }

    /**
     * Reset the company settings to default values.
     */
    public function reset(): JsonResponse
    {
        $setting = CompanySetting::getSingleton();
        $defaults = CompanySetting::defaultAttributes();

        $setting->update($defaults);

        return response()->json([
            'status' => 'success',
            'message' => 'Informations de l\'entreprise réinitialisées aux valeurs d\'origine.',
            'data' => $this->formatUrls($setting->fresh()->toArray()),
        ]);
    }
}
