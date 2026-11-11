<?php

namespace Database\Seeders;

use App\Models\CatalogCategory;
use App\Models\CatalogMaterial;
use Illuminate\Database\Seeder;

/**
 * Types de matériaux + variétés supplémentaires.
 * Idempotent : rien d'existant (même slug) n'est écrasé.
 */
class CatalogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'marbre' => [
                'name' => 'Marbre',
                'description' => "Roche noble par excellence, le marbre se distingue par ses veinages uniques et sa lumière. Du blanc pur de Carrare au noir profond du Marquina, chaque variété apporte une signature différente à vos espaces.",
                'fallback_gradient' => 'linear-gradient(135deg, #f5f2eb 0%, #dfd9ce 100%)',
            ],
            'granit' => [
                'name' => 'Granit',
                'description' => "Extrêmement dur et résistant, le granit est le matériau idéal pour les plans de travail, les sols à fort passage et les extérieurs. Ses grains minéraux offrent une palette allant du blanc moucheté au noir intense.",
                'fallback_gradient' => 'linear-gradient(135deg, #1c1c1c 0%, #0d0d0d 100%)',
            ],
            'pierre-naturelle' => [
                'name' => 'Pierre naturelle',
                'description' => "Pierres calcaires et parements bruts, taillés ou éclatés : la pierre naturelle apporte authenticité et chaleur aux façades, murs et aménagements extérieurs.",
                'fallback_gradient' => 'linear-gradient(135deg, #d9c9a8 0%, #b9a47f 100%)',
            ],
            'onyx' => [
                'name' => 'Onyx',
                'description' => "Pierre translucide aux couleurs spectaculaires, l'onyx se prête aux réalisations rétroéclairées, comptoirs, salles de bain et pièces d'exception.",
                'fallback_gradient' => 'linear-gradient(135deg, #5f8a6e 0%, #2f4a3a 100%)',
            ],
            'travertin' => [
                'name' => 'Travertin',
                'description' => "Reconnaissable à ses strates et à ses pores naturels, le travertin offre des tons chauds et intemporels, en intérieur comme autour des piscines.",
                'fallback_gradient' => 'linear-gradient(135deg, #ded3c1 0%, #c4b59f 100%)',
            ],
        ];

        $position = (int) CatalogCategory::max('position');
        $ids = [];

        foreach ($categories as $slug => $attrs) {
            $category = CatalogCategory::where('slug', $slug)->orWhere('name', $attrs['name'])->first();

            if (! $category) {
                $category = CatalogCategory::create($attrs + [
                    'slug' => $slug,
                    'position' => ++$position,
                    'is_active' => true,
                ]);
            } elseif (empty($category->description)) {
                $category->update(['description' => $attrs['description']]);
            }

            $ids[$attrs['name']] = $category->id;
        }

        // Rattache les variétés existantes sans type à la catégorie correspondant à leur colonne `type`.
        CatalogMaterial::whereNull('category_id')->get()->each(function ($m) use ($ids) {
            $key = $m->type ?: $m->name;
            if (isset($ids[$key])) {
                $m->update(['category_id' => $ids[$key]]);
            }
        });

        $varieties = [
            ['Marbre', 'Statuario', 'marbre-statuario', 'Italie (Carrare)', 'Poli, adouci', 'linear-gradient(135deg, #fbfaf7 0%, #e4e1da 55%, #c9c5bd 100%)',
                "Blanc éclatant traversé de larges veines grises très graphiques : le Statuario est l'un des marbres les plus recherchés pour les pièces d'exception."],
            ['Marbre', 'Blanc de Carrare', 'marbre-blanc-de-carrare', 'Italie (Carrare)', 'Poli, adouci, satiné', 'linear-gradient(135deg, #eeede9 0%, #d8d6d0 100%)',
                "Fond blanc-gris parcouru de veines fines et diffuses : un classique élégant et polyvalent pour sols, murs et salles de bain."],
            ['Marbre', 'Nero Marquina', 'marbre-nero-marquina', 'Espagne', 'Poli, adouci', 'linear-gradient(135deg, #1b1b1d 0%, #0a0a0b 100%)',
                "Noir profond strié de veines blanches contrastées : un marbre spectaculaire pour des intérieurs au caractère affirmé."],
            ['Marbre', 'Emperador Dark', 'marbre-emperador-dark', 'Espagne', 'Poli', 'linear-gradient(135deg, #5a4232 0%, #33241a 100%)',
                "Brun chocolat parcouru de fines veines claires : chaleureux et raffiné, idéal pour sols, plans vasques et habillages."],
            ['Granit', 'Star Galaxy', 'granit-star-galaxy', 'Inde', 'Poli', 'linear-gradient(135deg, #191919 0%, #0b0b0b 100%)',
                "Noir constellé de paillettes dorées et cuivrées qui scintillent à la lumière : très résistant, parfait pour les cuisines."],
            ['Granit', 'Kashmir White', 'granit-kashmir-white', 'Inde', 'Poli, flammé', 'linear-gradient(135deg, #ece9e4 0%, #cfcac2 100%)',
                "Fond blanc crème moucheté de grenats et de points gris : lumineux et durable, pour plans de travail et sols."],
            ['Pierre naturelle', 'Pierre de Taza', 'pierre-naturelle-pierre-de-taza', 'Taza, Maroc', 'Taillée, éclatée, bouchardée', 'linear-gradient(135deg, #e3d5b8 0%, #c6b18b 100%)',
                "Calcaire doux aux tons dorés, extrait près de Taza : la pierre emblématique de nos façades et murs en parement."],
            ['Onyx', 'Miel', 'onyx-miel', 'Iran / Pakistan', 'Poli', 'linear-gradient(135deg, #e8b46a 0%, #a8662a 100%)',
                "Teintes ambrées et miel aux ondulations translucides : sublime en rétroéclairage pour comptoirs et murs d'accent."],
            ['Onyx', 'Blanc', 'onyx-blanc', 'Iran', 'Poli', 'linear-gradient(135deg, #f6f1e7 0%, #ddd2bd 100%)',
                "Blanc laiteux aux voiles crème, d'une grande translucidité : pour des réalisations lumineuses et épurées."],
            ['Travertin', 'Noce', 'travertin-noce', 'Turquie', 'Adouci, vieilli', 'linear-gradient(135deg, #a77d58 0%, #6f4e33 100%)',
                "Tons noyer chauds et strates marquées : un travertin chaleureux pour sols, terrasses et façades."],
            ['Travertin', 'Silver', 'travertin-silver', 'Turquie', 'Adouci, brossé', 'linear-gradient(135deg, #c9c6bf 0%, #8f8b83 100%)',
                "Nuances grises argentées veinées de crème : une version contemporaine et sobre du travertin."],
        ];

        $matPosition = (int) CatalogMaterial::max('position');

        foreach ($varieties as [$type, $variant, $slug, $origin, $finish, $gradient, $description]) {
            if (! isset($ids[$type]) || CatalogMaterial::where('slug', $slug)->exists()) {
                continue;
            }

            CatalogMaterial::create([
                'slug' => $slug,
                'category_id' => $ids[$type],
                'position' => ++$matPosition,
                'type' => $type,
                'name' => $type,
                'variant' => $variant,
                'description' => $description,
                'image' => null,
                'hero_image' => null,
                'gallery' => [],
                'origin' => $origin,
                'finish' => $finish,
                'thickness' => '2 cm, 3 cm (autres sur demande)',
                'uses' => ['Sols', 'Revêtements muraux', 'Plans de travail', 'Salles de bain'],
                'maintenance' => 'Nettoyage au savon neutre ; éviter les produits acides. Traitement hydrofuge recommandé.',
                'fallback_gradient' => $gradient,
                'is_active' => true,
            ]);
        }
    }
}

