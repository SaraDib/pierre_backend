<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $table = 'company_settings';

    protected $guarded = [];

    /**
     * Default company values.
     */
    public static function defaultAttributes(): array
    {
        return [
            'name' => 'SUTRAVOMAR',
            'tagline' => 'STONE LUXE',
            'description' => 'Marbre, granit et pierre naturelle : sélection, taille, vente et pose partout au Maroc.',
            'logo_url' => '/logo-gold.png',
            'logo_footer_url' => null,
            'phone' => '+212 6 00 00 00 00',
            'phone_secondary' => null,
            'whatsapp_number' => '212600000000',
            'email' => 'contact@sutravomar-stone.ma',
            'address' => 'Atelier & Showroom, Maroc',
            'city' => 'Casablanca',
            'country' => 'Maroc',
            'opening_hours' => 'Lun - Sam : 08h30 - 18h30',
            'intervention_zone' => 'Intervention dans tout le Maroc',
            'instagram_url' => 'https://instagram.com',
            'facebook_url' => 'https://facebook.com',
            'linkedin_url' => null,
            'maps_url' => null,
            'copyright' => '© ' . date('Y') . ' SUTRAVOMAR STONE LUXE. Tous droits réservés.',
        ];
    }

    /**
     * Get or initialize the singleton CompanySetting row.
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
