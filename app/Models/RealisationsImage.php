<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisationsImage extends Model
{
    protected $table = 'realisations_images';

    protected $guarded = [];

    protected $casts = [
        'position' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(RealisationsItem::class, 'realisation_item_id');
    }
}
