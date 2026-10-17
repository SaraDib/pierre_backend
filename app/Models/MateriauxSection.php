<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriauxSection extends Model
{
    protected $table = 'materiaux_sections';

    protected $guarded = [];

    /**
     * Get the singleton header row (created empty if missing).
     */
    public static function getSingleton(): self
    {
        return self::first() ?? self::create([]);
    }
}
