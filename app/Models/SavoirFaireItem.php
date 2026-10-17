<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavoirFaireItem extends Model
{
    protected $table = 'savoir_faire_items';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];
}
