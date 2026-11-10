<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogMaterial extends Model
{
    protected $table = 'catalog_materials';

    protected $guarded = [];

    protected $casts = [
        'gallery' => 'array',
        'uses' => 'array',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];
}

