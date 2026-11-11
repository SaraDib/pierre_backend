<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Variété d'un type de matériau (ex : Marbre → Calacatta).
 */
class CatalogMaterial extends Model
{
    protected $table = 'catalog_materials';

    protected $guarded = [];

    protected $casts = [
        'gallery' => 'array',
        'uses' => 'array',
        'is_active' => 'boolean',
        'position' => 'integer',
        'category_id' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'category_id');
    }
}
