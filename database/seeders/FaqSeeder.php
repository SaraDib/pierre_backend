<?php

namespace Database\Seeders;

use App\Models\FaqCategory;
use App\Models\FaqItem;
use App\Models\FaqSection;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Singleton section
        FaqSection::updateOrCreate(
            ['id' => 1],
            [
                'eyebrow' => 'Questions fréquentes',
                'title' => 'Tout ce que vous',
                'title_em' => 'devez savoir.',
                'description' => 'Vous hésitez sur le choix de votre pierre ? Contactez directement nos tailleurs de pierre pour un conseil sur mesure.',
                'cta_text' => 'Poser une question',
                'cta_link' => null,
            ]
        );

        // 2. Categories
        $categoriesData = [
            [
                'slug' => 'choix-pierres',
                'name' => 'Choix des pierres',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'slug' => 'fourniture-pose',
                'name' => 'Fourniture & Pose',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'slug' => 'delais',
                'name' => 'Délais & Livraison',
                'position' => 3,
                'is_active' => true,
            ],
            [
                'slug' => 'entretien',
                'name' => 'Entretien',
                'position' => 4,
                'is_active' => true,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = FaqCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // 3. FAQ Items (from existing FAQ_DATA)
        $items = [
            [
                'category_slug' => 'choix-pierres',
                'question' => 'Comment choisir entre marbre, granit et travertin ?',
                'answer' => "Le marbre est privilégié pour son esthétique noble et ses veines uniques en intérieur (escaliers, salles de bain, sols de réception). Le granit, d'une dureté incomparable, est recommandé pour les cuisines et zones de fort passage. Le travertin et la pierre de Taza sont idéaux pour les terrasses, margelles de piscine et façades extérieures.",
                'position' => 1,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'category_slug' => 'fourniture-pose',
                'question' => 'Proposez-vous la fourniture seule ou avec la pose ?',
                'answer' => "Nous assurons la prise en charge complète : conseil technique, sélection des blocs ou tranches en carrière, découpe sur mesure dans notre atelier et pose par nos maîtres artisans qualifiés partout au Maroc. Nous pouvons également livrer les tranches débitées selon vos calepinages.",
                'position' => 2,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'category_slug' => 'fourniture-pose',
                'question' => 'Intervenez-vous partout au Maroc ?',
                'answer' => "Oui, nous livrons et réalisons des chantiers dans tout le Royaume : Casablanca, Rabat, Marrakech, Tanger, Fès, Agadir, etc. Nos équipes se déplacent pour la prise de cotes au laser et l'installation.",
                'position' => 3,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'category_slug' => 'delais',
                'question' => 'Quels sont les délais de fabrication et de livraison ?',
                'answer' => "Selon la nature du projet et la rareté du matériau choisi, nos délais varient généralement entre 10 et 25 jours ouvrés à compter de la validation des cotes et du calepinage technique.",
                'position' => 4,
                'is_featured' => true,
                'is_active' => true,
            ],
            [
                'category_slug' => 'entretien',
                'question' => 'Comment entretenir une surface en pierre naturelle ?',
                'answer' => "Nous appliquons systématiquement un traitement hydrofuge et oléofuge de qualité professionnelle à la livraison. Pour l'entretien quotidien, de l'eau tiède avec un savon au pH neutre (savon noir ou de Marseille) suffit amplement. Évitez tous les produits acides (vinaigre, anticalcaire, javel).",
                'position' => 5,
                'is_featured' => true,
                'is_active' => true,
            ],
        ];

        foreach ($items as $itemData) {
            $catSlug = $itemData['category_slug'];
            unset($itemData['category_slug']);
            $itemData['category_id'] = $categories[$catSlug]->id ?? null;

            FaqItem::updateOrCreate(
                ['question' => $itemData['question']],
                $itemData
            );
        }
    }
}
