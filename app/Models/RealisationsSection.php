<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealisationsSection extends Model
{
    protected $table = 'realisations_sections';

    protected $guarded = [];

    /**
     * Get the singleton row (created empty if missing).
     */
    public static function getSingleton(): self
    {
        return self::first() ?? self::create([]);
    }
}
