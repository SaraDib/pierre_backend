<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriauxItem extends Model
{
    protected $table = 'materiaux_items';

    protected $guarded = [];

    protected $casts = [
        'finishes' => 'array',
        'is_active' => 'boolean',
        'position' => 'integer',
        'seed' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MateriauxCategory::class, 'category_id');
    }

    public function realisations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            RealisationsItem::class,
            'realisations_materiaux',
            'materiaux_item_id',
            'realisation_item_id'
        )->withTimestamps();
    }
}
