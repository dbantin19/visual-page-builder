<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavMenuItem extends Model
{
    protected $fillable = ['label', 'page_id', 'url', 'parent_id', 'sort_order'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavMenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NavMenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function resolvedUrl(): string
    {
        if ($this->page_id && $this->page) {
            return '/' . $this->page->slug;
        }
        return $this->url ?? '#';
    }
}
