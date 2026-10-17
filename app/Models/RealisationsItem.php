<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RealisationsItem extends Model
{
    protected $table = 'realisations_items';

    protected $guarded = [];

    protected $casts = [
        'position' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RealisationsCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(RealisationsImage::class, 'realisation_item_id')->orderBy('position')->orderBy('id');
    }

    public function materiaux(): BelongsToMany
    {
        return $this->belongsToMany(
            MateriauxItem::class,
            'realisations_materiaux',
            'realisation_item_id',
            'materiaux_item_id'
        )->withTimestamps();
    }
}
