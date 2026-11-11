<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Type de matériau (Marbre, Granit, Onyx…) regroupant plusieurs variétés.
 */
class CatalogCategory extends Model
{
    protected $table = 'catalog_categories';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(CatalogMaterial::class, 'category_id')
            ->orderBy('position')
            ->orderBy('id');
    }
}

