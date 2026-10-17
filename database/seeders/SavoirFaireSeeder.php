<?php

namespace Database\Seeders;

use App\Models\SavoirFaireItem;
use App\Models\SavoirFaireSection;
use Illuminate\Database\Seeder;

class SavoirFaireSeeder extends Seeder
{
    /**
     * Seed the Savoir-faire section with the original page content.
     */
    public function run(): void
    {
        SavoirFaireSection::query()->delete();
        SavoirFaireSection::create([
            'eyebrow' => 'Notre expertise',
            'title' => 'Cinq métiers,',
            'title_em' => 'une seule exigence.',
            'description' => 'De la sélection du bloc à la dernière jointure, nous maîtrisons chaque couche du projet.',
        ]);

        $items = [
            ['granit', 'Revêtements muraux & sols', 'Marbre et pierre naturelle posés avec précision, pour des espaces élégants et durables.'],
            ['marbre', 'Plans de travail & vasques', 'Cuisines et salles de bain taillées sur mesure dans des matériaux haut de gamme.'],
            ['taza', 'Escaliers & façades', "Habillage d'escaliers, de façades et de piscines en pierre de Taza, robuste et raffinée."],
            ['travertin', 'Aménagements extérieurs', 'Dallages, terrasses, murets et jardins résistants aux intempéries.'],
            ['ardoise', 'Vente & livraison', 'Fourniture de pierres naturelles et livraison sécurisée sur chantier, partout au Maroc.'],
        ];

        SavoirFaireItem::query()->delete();
        foreach ($items as $i => [$mat, $title, $desc]) {
            SavoirFaireItem::create([
                'position' => $i + 1,
                'mat_key' => $mat,
                'title' => $title,
                'description' => $desc,
                'is_active' => true,
            ]);
        }
    }
}
