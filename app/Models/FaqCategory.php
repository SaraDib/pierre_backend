<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqCategory extends Model
{
    protected $table = 'faq_categories';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FaqItem::class, 'category_id');
    }
}
