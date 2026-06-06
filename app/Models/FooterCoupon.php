<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FooterCoupon extends Model
{
    protected $fillable = [
        'kicker',
        'headline',
        'description',
        'fine_print',
        'expires_enabled',
        'expires_end_of_month',
        'expires_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'expires_enabled' => 'boolean',
            'expires_end_of_month' => 'boolean',
            'expires_at' => 'date',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function resolvedExpiresAt(): ?Carbon
    {
        if (! $this->expires_enabled) {
            return null;
        }

        if ($this->expires_end_of_month) {
            return now()->endOfMonth();
        }

        return $this->expires_at;
    }

    public function resolvedExpiryLabel(): ?string
    {
        return $this->resolvedExpiresAt()?->format('F j, Y');
    }
}
