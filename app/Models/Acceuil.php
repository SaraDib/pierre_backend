<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acceuil extends Model
{
    use HasFactory;

    protected $table = 'acceuil';

    protected $guarded = [];

    protected $casts = [
        'marquee_words' => 'array',
        'features_bar' => 'array',
    ];

    public static function defaultFeaturesBar(): array
    {
        return [
            [
                'icon' => 'star',
                'title' => 'Pierres naturelles',
                'subtitle' => 'Qualité supérieure garantie',
            ],
            [
                'icon' => 'shield',
                'title' => 'Savoir-faire',
                'subtitle' => "Une équipe d'experts à votre service",
            ],
            [
                'icon' => 'arrow',
                'title' => 'Livraison & Pose',
                'subtitle' => "Partout au Maroc et à l'international",
            ],
            [
                'icon' => 'clock',
                'title' => 'Engagement durable',
                'subtitle' => 'Respect de la nature et des générations futures',
            ],
        ];
    }

    /**
     * Get or initialize the singleton Acceuil content row.
     */
    public static function getSingleton(): self
    {
        $item = self::first();

        if (! $item) {
            $item = self::create([
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
                'features_bar' => self::defaultFeaturesBar(),
            ]);
        } elseif (empty($item->features_bar)) {
            $item->features_bar = self::defaultFeaturesBar();
            $item->save();
        }

        return $item;
    }
}
