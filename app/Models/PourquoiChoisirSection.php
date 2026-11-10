<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PourquoiChoisirSection extends Model
{
    protected $table = 'pourquoi_choisir_sections';

    protected $guarded = [];

    protected $casts = [
        'categories' => 'array',
    ];

    public static function defaultCategories(): array
    {
        return [
            [
                'id' => 1,
                'icon' => 'home',
                'line1' => 'Maisons',
                'line2' => '& villas',
                'is_active' => true,
            ],
            [
                'id' => 2,
                'icon' => 'hotel',
                'line1' => 'Hôtels',
                'line2' => '& restaurants',
                'is_active' => true,
            ],
            [
                'id' => 3,
                'icon' => 'shop',
                'line1' => 'Commerces',
                'line2' => '& boutiques',
                'is_active' => true,
            ],
            [
                'id' => 4,
                'icon' => 'users',
                'line1' => 'Espaces publics',
                'line2' => '& professionnels',
                'is_active' => true,
            ],
        ];
    }

    public static function defaultAttributes(): array
    {
        return [
            'eyebrow' => 'POURQUOI NOUS CHOISIR ?',
            'title_line1' => "L'excellence",
            'title_line2' => 'au cœur de chaque projet.',
            'description' => "Chez SUTRAVOMAR, nous ne vendons pas seulement des pierres, nous créons des espaces intemporels. Chaque projet est une rencontre entre la nature, l'architecture et votre vision.",
            'stat1_number' => '+500',
            'stat1_label' => 'Projets réalisés',
            'stat2_number' => '100%',
            'stat2_label' => 'Clients satisfaits',
            'stat3_number' => '15 ans',
            'stat3_label' => "D'expérience",
            'button_text' => 'En savoir plus',
            'button_link' => '#materiaux',
            'image' => '/images/pourquoi-nous-choisir-escalier.jpg',
            'image_alt' => 'Escalier monumental en marbre avec éclairage LED sous chaque marche',
            'quote_line1' => 'La pierre',
            'quote_line2' => 'révèle le caractère',
            'quote_line3' => 'de vos espaces',
            'categories' => self::defaultCategories(),
        ];
    }

    public static function getSingleton(): self
    {
        $item = self::first();

        if (! $item) {
            $item = self::create(self::defaultAttributes());
        } elseif (empty($item->categories)) {
            $item->categories = self::defaultCategories();
            $item->save();
        }

        return $item;
    }
}

