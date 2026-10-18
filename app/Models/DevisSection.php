<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevisSection extends Model
{
    use HasFactory;

    protected $table = 'devis_section';

    protected $guarded = [];

    /**
     * Default section attributes.
     */
    public static function defaultAttributes(): array
    {
        return [
            'eyebrow' => 'Devis sur mesure',
            'title_prefix' => 'Donnez vie à',
            'title_emphasis' => 'vos projets de pierre.',
            'description' => 'Partagez-nous les dimensions, surfaces et inspirations de votre ouvrage. Notre bureau d\'études vous transmet une estimation détaillée et personnalisée.',
            'stat1_value' => '24h',
            'stat1_label' => 'Délai de réponse',
            'stat2_value' => 'Gratuit',
            'stat2_label' => 'Étude & chiffrage',
            'button_text' => 'Envoyer via WhatsApp',
            'note_text' => 'Le message se prépare automatiquement dans WhatsApp, prêt à être expédié.',
        ];
    }

    /**
     * Get or initialize the singleton DevisSection row.
     */
    public static function getSingleton(): self
    {
        $item = self::first();

        if (! $item) {
            $item = self::create(self::defaultAttributes());
        }

        return $item;
    }
}
