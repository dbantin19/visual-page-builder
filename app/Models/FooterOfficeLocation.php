<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FooterOfficeLocation extends Model
{
    protected $fillable = [
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'region',
        'postal_code',
        'phone',
        'link_url',
        'sort_order',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function cityLine(): ?string
    {
        $cityRegion = collect([
            $this->city,
            $this->region,
        ])
            ->filter(fn(?string $part) => filled($part))
            ->implode(', ');

        return collect([
            $cityRegion,
            $this->postal_code,
        ])
            ->filter(fn(?string $part) => filled($part))
            ->implode(' ') ?: null;
    }

    public function phoneHref(): ?string
    {
        return FooterSetting::phoneHref($this->phone);
    }

    public function linkHref(): ?string
    {
        $link = trim($this->link_url ?? '');

        if ($link === '' || preg_match('/^\s*javascript:/i', $link)) {
            return null;
        }

        return $link;
    }
}
