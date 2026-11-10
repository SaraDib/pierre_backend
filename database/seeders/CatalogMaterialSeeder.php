<?php

namespace Database\Seeders;

use App\Models\CatalogMaterial;
use App\Models\MateriauxSection;
use Illuminate\Database\Seeder;

/**
 * Matériaux initiaux du catalogue (reprend les données par défaut du frontend).
 * Idempotent : un matériau existant (même slug) n'est jamais écrasé.
 */
class CatalogMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $section = MateriauxSection::getSingleton();
        if (empty($section->eyebrow) || $section->eyebrow === 'Collections') {
            $section->update([
                'eyebrow' => 'NOS MATÉRIAUX',
                'title' => "Une sélection d'exception",
                'title_em' => '',
                'description' => 'Marbre, granit, pierre naturelle... Des matériaux nobles, soigneusement sélectionnés pour leur beauté, leur résistance et leur durabilité.',
            ]);
        }
        $materials = [
            [
                'slug' => 'marbre-calacatta',
                'type' => 'Marbre',
                'name' => 'Marbre',
                'variant' => 'Calacatta',
                'image' => '/images/mat-marbre-calacatta-texture.jpg',
                'hero_image' => '/images/mat-marbre-calacatta-texture.jpg',
                'gallery' => [
                    ['src' => '/images/mat-marbre-calacatta-texture.jpg', 'alt' => 'Texture du marbre Calacatta : fond blanc, veines grises et dorées'],
                    ['src' => '/images/mat-marbre-calacatta-detail.jpg', 'alt' => 'Détail d’une dalle de marbre Calacatta polie'],
                    ['src' => '/images/realisation-escalier-marbre.jpg', 'alt' => 'Escalier monumental en marbre blanc veiné'],
                    ['src' => '/images/realisation-salle-de-bain.jpg', 'alt' => 'Salle de bain habillée de marbre et de pierre naturelle'],
                ],
                'description' => "Le Calacatta est l'un des marbres les plus prestigieux : un fond d'un blanc pur traversé de veines grises franches et de nuances dorées. Chaque dalle est unique et apporte une signature de luxe à vos intérieurs.",
                'origin' => 'Carrare, Italie',
                'finish' => 'Poli, adouci ou satiné',
                'thickness' => '2 cm, 3 cm (autres épaisseurs sur demande)',
                'uses' => ['Escaliers', 'Plans de travail', 'Salles de bain', 'Sols et revêtements muraux'],
                'maintenance' => 'Nettoyage au savon neutre ; éviter les produits acides. Traitement hydrofuge recommandé.',
                'fallback_gradient' => 'linear-gradient(135deg, #f5f2eb 0%, #dfd9ce 100%)',
            ],
            [
                'slug' => 'granit-noir-absolu',
                'type' => 'Granit',
                'name' => 'Granit',
                'variant' => 'Noir Absolu',
                'image' => '/images/mat-granit-noir-absolu-texture.jpg',
                'hero_image' => '/images/mat-granit-noir-absolu-texture.jpg',
                'gallery' => [
                    ['src' => '/images/mat-granit-noir-absolu-texture.jpg', 'alt' => 'Texture du granit Noir Absolu : noir profond finement moucheté'],
                    ['src' => '/images/mat-granit-noir-absolu-detail.jpg', 'alt' => 'Détail d’une dalle de granit Noir Absolu poli'],
                    ['src' => '/images/realisation-cuisine-granit.jpg', 'alt' => 'Cuisine avec îlot central en granit noir'],
                    ['src' => '/images/materiaux-slabs-composite.jpg', 'alt' => 'Nuancier des matériaux SUTRAVOMAR : marbre, granit, travertin et onyx'],
                ],
                'description' => "D'un noir intense et uniforme, le Noir Absolu séduit par sa finesse de grain et ses discrets éclats minéraux. Très résistant, il s'impose dans les cuisines et les espaces de passage intense.",
                'origin' => 'Inde / Afrique du Sud',
                'finish' => 'Poli, flammé ou adouci',
                'thickness' => '2 cm, 3 cm',
                'uses' => ['Plans de travail de cuisine', 'Vasques', 'Sols', 'Seuils et marches'],
                'maintenance' => 'Entretien très simple à l’eau et au savon doux ; résistant aux rayures et à la chaleur.',
                'fallback_gradient' => 'linear-gradient(135deg, #1c1c1c 0%, #0d0d0d 100%)',
            ],
            [
                'slug' => 'pierre-naturelle-travertin-eclatee',
                'type' => 'Pierre naturelle',
                'name' => 'Pierre naturelle',
                'variant' => 'Travertin éclatée',
                'image' => '/images/mat-pierre-eclatee-block-card.jpg',
                'hero_image' => null,
                'gallery' => [
                    ['src' => '/images/mat-pierre-eclatee-block.jpg', 'alt' => 'Parement de travertin éclaté, face brute'],
                    ['src' => '/images/pierre-eclatee-taza.jpg', 'alt' => 'Module de pierre éclatée de Taza'],
                    ['src' => '/images/realisation-facade-taza.jpg', 'alt' => 'Façade habillée de pierre de Taza'],
                ],
                'description' => 'La pierre éclatée est obtenue par fendage : sa face brute et irrégulière offre un relief chaleureux et authentique. Elle habille façades, murs et aménagements extérieurs avec un caractère naturel marqué.',
                'origin' => 'Taza, Maroc',
                'finish' => 'Éclaté (face brute de fendage)',
                'thickness' => '2 à 4 cm selon le format',
                'uses' => ['Façades', 'Murs intérieurs et extérieurs', 'Cheminées', 'Aménagements de jardin'],
                'maintenance' => 'Brossage à sec et rinçage à l’eau ; hydrofuge conseillé en extérieur.',
                'fallback_gradient' => 'linear-gradient(135deg, #d8be9b 0%, #b89870 100%)',
            ],
            [
                'slug' => 'onyx-vert',
                'type' => 'Onyx',
                'name' => 'Onyx',
                'variant' => 'Vert',
                'image' => '/images/mat-onyx-vert-card.jpg',
                'hero_image' => '/images/mat-onyx-vert-hero.jpg',
                'gallery' => [
                    ['src' => '/images/mat-onyx-vert-hero.jpg', 'alt' => 'Texture de l’onyx vert translucide aux veines ambrées'],
                    ['src' => '/images/mat-onyx-vert-detail.jpg', 'alt' => 'Détail d’une dalle d’onyx vert poli'],
                    ['src' => '/images/materiaux-slabs-composite.jpg', 'alt' => 'Nuancier des matériaux SUTRAVOMAR : marbre, granit, travertin et onyx'],
                    ['src' => '/images/realisation-salle-de-bain.jpg', 'alt' => 'Salle de bain en pierre naturelle et marbre'],
                ],
                'description' => "L'onyx vert est une pierre translucide aux veines blanches et ambrées. Rétro-éclairé, il révèle toute sa profondeur : un matériau d'exception pour des créations décoratives uniques.",
                'origin' => 'Iran / Pakistan',
                'finish' => 'Poli brillant',
                'thickness' => '2 cm (rétro-éclairage : 1,5 cm collé sur support)',
                'uses' => ['Panneaux rétro-éclairés', 'Comptoirs et bars', 'Vasques', 'Décoration murale'],
                'maintenance' => 'Pierre délicate : chiffon doux et eau uniquement ; éviter tout produit abrasif ou acide.',
                'fallback_gradient' => 'linear-gradient(135deg, #284b3e 0%, #173229 100%)',
            ],
            [
                'slug' => 'travertin-classique',
                'type' => 'Travertin',
                'name' => 'Travertin',
                'variant' => 'Classique',
                'image' => '/images/mat-travertin-classique-card.jpg',
                'hero_image' => null,
                'gallery' => [
                    ['src' => '/images/mat-travertin-classique.jpg', 'alt' => 'Texture du travertin classique crème aux strates horizontales'],
                    ['src' => '/images/mat-travertin-classique-detail.jpg', 'alt' => 'Détail d’une dalle de travertin classique'],
                    ['src' => '/images/mat-pierre-travertin.jpg', 'alt' => 'Dalle de travertin beige doré'],
                    ['src' => '/images/realisation-piscine-travertin.jpg', 'alt' => 'Plage de piscine en travertin vieilli'],
                ],
                'description' => 'Le travertin classique séduit par ses strates horizontales et ses tons crème chaleureux. Intemporel, il se décline en sols, margelles et revêtements, en intérieur comme en extérieur.',
                'origin' => 'Maroc / Turquie',
                'finish' => 'Adouci, vieilli ou brossé',
                'thickness' => '2 cm, 3 cm',
                'uses' => ['Terrasses et margelles de piscine', 'Sols intérieurs', 'Salles de bain', 'Revêtements muraux'],
                'maintenance' => 'Nettoyage au savon neutre ; protéger avec un hydrofuge et reboucher les pores si nécessaire.',
                'fallback_gradient' => 'linear-gradient(135deg, #ded3c1 0%, #c4b59f 100%)',
            ],
        ];

        foreach ($materials as $index => $material) {
            CatalogMaterial::firstOrCreate(
                ['slug' => $material['slug']],
                $material + ['position' => $index + 1, 'is_active' => true]
            );
        }
    }
}

