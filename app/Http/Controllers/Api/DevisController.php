<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DevisRequest;
use App\Models\DevisSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevisController extends Controller
{
    /**
     * Get the Devis section content (public).
     */
    public function getSection(): JsonResponse
    {
        $section = DevisSection::getSingleton();

        return response()->json([
            'status' => 'success',
            'data' => $section,
        ]);
    }

    /**
     * Update the Devis section content (admin).
     */
    public function updateSection(Request $request): JsonResponse
    {
        $section = DevisSection::getSingleton();

        $data = $request->validate([
            'eyebrow' => ['required', 'string', 'max:255'],
            'title_prefix' => ['required', 'string', 'max:255'],
            'title_emphasis' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stat1_value' => ['required', 'string', 'max:50'],
            'stat1_label' => ['required', 'string', 'max:100'],
            'stat2_value' => ['required', 'string', 'max:50'],
            'stat2_label' => ['required', 'string', 'max:100'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'note_text' => ['nullable', 'string', 'max:255'],
        ]);

        $section->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Section Devis mise à jour avec succès.',
            'data' => $section->fresh(),
        ]);
    }

    /**
     * Store a new quote request from the website (public).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:100'],
            'project_type' => ['nullable', 'string', 'max:150'],
            'message' => ['nullable', 'string'],
        ]);

        $data['ip_address'] = $request->ip();
        $data['status'] = 'nouveau';

        $devis = DevisRequest::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Demande de devis enregistrée avec succès.',
            'data' => $devis,
        ], 201);
    }

    /**
     * List all quote requests (admin).
     */
    public function index(Request $request): JsonResponse
    {
        $query = DevisRequest::query()->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('phone', 'like', $search)
                  ->orWhere('project_type', 'like', $search)
                  ->orWhere('message', 'like', $search);
            });
        }

        $items = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    /**
     * Get statistics on quote requests (admin).
     */
    public function stats(): JsonResponse
    {
        $total = DevisRequest::count();
        $nouveau = DevisRequest::where('status', 'nouveau')->count();
        $enCours = DevisRequest::where('status', 'en_cours')->count();
        $traite = DevisRequest::where('status', 'traite')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $total,
                'nouveau' => $nouveau,
                'en_cours' => $enCours,
                'traite' => $traite,
            ],
        ]);
    }

    /**
     * Update a quote request status or notes (admin).
     */
    public function update(Request $request, DevisRequest $devi): JsonResponse
    {
        $data = $request->validate([
            'status' => ['nullable', 'string', 'in:nouveau,en_cours,traite,archive'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $devi->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Demande de devis mise à jour.',
            'data' => $devi->fresh(),
        ]);
    }

    /**
     * Delete a quote request (admin).
     */
    public function destroy(DevisRequest $devi): JsonResponse
    {
        $devi->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Demande de devis supprimée avec succès.',
        ]);
    }
}
