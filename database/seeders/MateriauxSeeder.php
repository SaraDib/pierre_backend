<?php

namespace Database\Seeders;

use App\Models\MateriauxCategory;
use App\Models\MateriauxItem;
use App\Models\MateriauxSection;
use Illuminate\Database\Seeder;

class MateriauxSeeder extends Seeder
{
    /**
     * Seed the Matériaux section with the original page content.
     */
    public function run(): void
    {
        MateriauxSection::query()->delete();
        MateriauxSection::create([
            'eyebrow' => 'Collections',
            'title' => 'Choisissez votre',
            'title_em' => 'matière.',
            'description' => 'Touchez, comparez, imaginez. Sélectionnez une pierre puis une finition pour la visualiser.',
        ]);

        MateriauxItem::query()->delete();
        MateriauxCategory::query()->delete();

        $categories = [];
        foreach ([
            ['interieur', 'Intérieur'],
            ['interieur-exterieur', 'Int. & ext.'],
            ['facades', 'Façades'],
        ] as $i => [$slug, $name]) {
            $categories[$slug] = MateriauxCategory::create([
                'slug' => $slug,
                'name' => $name,
                'position' => $i + 1,
                'is_active' => true,
            ])->id;
        }

        // [slug, category, name, description, finishes, type, base, vein, tone, freq, seed, visible in selector]
        $items = [
            ['marbre', 'interieur', 'Marbre',
                "Veines profondes et brillance noble : le choix des escaliers, sols de réception, vasques et plans de travail d'exception.",
                ['Poli', 'Adouci', 'Brossé'], 'veins', '#e9e4da', '#5d5a55', '#bfb7a6', '0.007 0.016', 4, true],
            ['granit', 'interieur-exterieur', 'Granit',
                'Extrêmement dur et résistant. Idéal pour les cuisines, les seuils, les marches et tous les usages intensifs.',
                ['Poli', 'Flammé', 'Bouchardé'], 'granit', '#2a2a2c', '#d8d4cc', '#4a4a4e', '0.9', 8, true],
            ['travertin', 'interieur-exterieur', 'Travertin',
                'Teintes chaudes et stries horizontales douces. Parfait pour murs, salles de bain, terrasses et margelles.',
                ['Brossé', 'Adouci', 'Vieilli'], 'bands', '#dccbad', '#a98e66', '#efe3cb', '0.004 0.085', 2, true],
            ['taza', 'facades', 'Pierre de Taza',
                'Calcaire compact aux nuances beige et gris, très résistant aux intempéries : façades, piscines, escaliers et dallages.',
                ['Bouchardé', 'Poli', 'Éclaté', 'Vieilli'], 'grain', '#cdb591', '#7d6445', '#e3d2b3', '0.5', 11, true],
            // Not part of the 4 selector tabs originally; still used as a texture elsewhere.
            ['ardoise', 'interieur-exterieur', 'Ardoise',
                "Roche métamorphique sombre aux reflets bleutés ou anthracites. Texture clivée naturelle pour revêtements muraux et salles d'eau.",
                ['Naturelle', 'Brossée', 'Vieillie'], 'bands', '#34383a', '#6e7a78', '#4b5254', '0.01 0.05', 6, false],
        ];

        foreach ($items as $i => [$slug, $cat, $name, $desc, $finishes, $type, $base, $vein, $tone, $freq, $seed, $visible]) {
            MateriauxItem::create([
                'category_id' => $categories[$cat],
                'slug' => $slug,
                'position' => $i + 1,
                'name' => $name,
                'description' => $desc,
                'finishes' => $finishes,
                'texture_type' => $type,
                'base' => $base,
                'vein' => $vein,
                'tone' => $tone,
                'freq' => $freq,
                'seed' => $seed,
                'is_active' => $visible,
            ]);
        }
    }
}
