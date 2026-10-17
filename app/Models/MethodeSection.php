<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MethodeSection extends Model
{
    protected $table = 'methode_sections';

    protected $guarded = [];

    /**
     * Get the singleton row (created empty if missing).
     */
    public static function getSingleton(): self
    {
        return self::first() ?? self::create([]);
    }
}
