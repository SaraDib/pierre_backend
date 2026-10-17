<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavoirFaireSection extends Model
{
    protected $table = 'savoir_faire_sections';

    protected $guarded = [];

    /**
     * Get the singleton header row (null if not seeded yet).
     */
    public static function getSingleton(): self
    {
        return self::first() ?? self::create([]);
    }
}
