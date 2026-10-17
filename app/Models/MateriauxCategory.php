<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MateriauxCategory extends Model
{
    protected $table = 'materiaux_categories';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MateriauxItem::class, 'category_id');
    }
}
