<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class)->latest();
    }

    protected $fillable = [
        'name',
        'slug',
        'meta_title',
        'meta_description',
        'is_published',
        'is_indexed',
        'head_section',
        'body_section',
        'content',
        'builder_data',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_indexed' => 'boolean',
        ];
    }
}
