<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RealisationsCategory extends Model
{
    protected $table = 'realisations_categories';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RealisationsItem::class, 'category_id');
    }
}
