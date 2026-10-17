<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MethodeItem extends Model
{
    protected $table = 'methode_items';

    protected $guarded = [];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];
}
