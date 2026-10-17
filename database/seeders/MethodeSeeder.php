<?php

namespace Database\Seeders;

use App\Models\MethodeItem;
use App\Models\MethodeSection;
use Illuminate\Database\Seeder;

class MethodeSeeder extends Seeder
{
    /**
     * Seed the Méthode tables with initial content.
     */
    public function run(): void
    {
        // 1. Singleton section header
        MethodeSection::query()->delete();
        MethodeSection::create([
            'eyebrow' => 'Notre méthode',
            'title' => 'Du bloc brut',
            'title_em' => "à l'ouvrage fini.",
            'description' => "De l'étude initiale à la pose finale, découvrez les 4 étapes de notre protocole d'artisanat d'excellence partout au Maroc.",
        ]);

        // 2. Steps
        MethodeItem::query()->delete();

        $steps = [
            [
                'step_number' => '01',
                'title' => 'Visite & métrage',
                'short_description' => 'Nous étudions votre projet sur place et relevons chaque dimension.',
                'description' => "Prise de cotes millimétrique sur votre chantier avec scanner laser et élaboration des plans d'appareillage sur mesure.",
                'duration' => '24h - 48h',
                'icon' => 'Ruler',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'step_number' => '02',
                'title' => 'Devis & choix',
                'short_description' => 'Devis détaillé et sélection des pierres et finitions avec vous.',
                'description' => 'Choix minutieux des carrières et des blocs de marbre ou pierre naturelle selon votre cahier des charges esthétique et technique.',
                'duration' => '24h - 72h',
                'icon' => 'FileText',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'step_number' => '03',
                'title' => 'Taille en atelier',
                'short_description' => 'Découpe, façonnage et contrôle qualité de chaque pièce.',
                'description' => 'Usinage 5 axes de haute précision dans notre atelier, complété par le travail de taille et de polissage traditionnel de nos maîtres artisans.',
                'duration' => '10 - 20 jours',
                'icon' => 'Hammer',
                'position' => 3,
                'is_active' => true,
            ],
            [
                'step_number' => '04',
                'title' => 'Livraison & pose',
                'short_description' => 'Transport sécurisé et pose soignée, joints et finitions inclus.',
                'description' => "Installation sur site avec joints invisibles, mise en place des ancrages et application d'un traitement hydrofuge / oléofuge longue durée.",
                'duration' => '3 - 7 jours',
                'icon' => 'Truck',
                'position' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($steps as $s) {
            MethodeItem::create($s);
        }
    }
}
