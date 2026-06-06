<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    public const DEFAULT_SECTION_ORDER = [
        'main_location',
        'office_locations',
        'coupons',
    ];

    public const ALIGNMENTS = [
        'left',
        'center',
        'right',
    ];

    public const DEFAULT_SECTION_ALIGNMENTS = [
        'main_location' => 'left',
        'office_locations' => 'left',
        'coupons' => 'left',
    ];

    public const DEFAULT_SECTION_CONTENT_ALIGNMENTS = [
        'main_location' => 'left',
        'office_locations' => 'left',
        'coupons' => 'left',
    ];

    protected $fillable = [
        'coupons_enabled',
        'location_enabled',
        'location_name',
        'location_address_line_1',
        'location_address_line_2',
        'location_city',
        'location_region',
        'location_postal_code',
        'location_phone',
        'section_order',
        'section_alignments',
        'section_content_alignments',
    ];

    protected function casts(): array
    {
        return [
            'coupons_enabled' => 'boolean',
            'location_enabled' => 'boolean',
            'section_order' => 'array',
            'section_alignments' => 'array',
            'section_content_alignments' => 'array',
        ];
    }

    public static function get(): self
    {
        return static::firstOrCreate([], [
            'coupons_enabled' => false,
            'location_enabled' => false,
            'section_order' => self::DEFAULT_SECTION_ORDER,
            'section_alignments' => self::DEFAULT_SECTION_ALIGNMENTS,
            'section_content_alignments' => self::DEFAULT_SECTION_CONTENT_ALIGNMENTS,
        ]);
    }

    public function normalizedSectionOrder(): array
    {
        $order = is_array($this->section_order) ? $this->section_order : [];

        return collect($order)
            ->filter(fn($section) => is_string($section) && in_array($section, self::DEFAULT_SECTION_ORDER, true))
            ->unique()
            ->merge(collect(self::DEFAULT_SECTION_ORDER)->diff($order))
            ->values()
            ->all();
    }

    public function normalizedSectionAlignments(): array
    {
        return $this->normalizedAlignmentMap($this->section_alignments, self::DEFAULT_SECTION_ALIGNMENTS);
    }

    public function normalizedSectionContentAlignments(): array
    {
        return $this->normalizedAlignmentMap(
            $this->section_content_alignments,
            self::DEFAULT_SECTION_CONTENT_ALIGNMENTS
        );
    }

    private function normalizedAlignmentMap(mixed $alignmentMap, array $defaults): array
    {
        $alignments = is_array($alignmentMap) ? $alignmentMap : [];

        return collect($defaults)
            ->map(function (string $default, string $section) use ($alignments) {
                $alignment = $alignments[$section] ?? $default;

                return in_array($alignment, self::ALIGNMENTS, true) ? $alignment : $default;
            })
            ->all();
    }

    public function hasLocationAddress(): bool
    {
        return $this->location_enabled && filled($this->location_address_line_1);
    }

    public function locationCityLine(): ?string
    {
        $cityRegion = collect([
            $this->location_city,
            $this->location_region,
        ])
            ->filter(fn(?string $part) => filled($part))
            ->implode(', ');

        return collect([
            $cityRegion,
            $this->location_postal_code,
        ])
            ->filter(fn(?string $part) => filled($part))
            ->implode(' ') ?: null;
    }

    public function locationPhoneHref(): ?string
    {
        return self::phoneHref($this->location_phone);
    }

    public static function phoneHref(?string $phone): ?string
    {
        $phone = trim($phone ?? '');

        if ($phone === '') {
            return null;
        }

        $prefix = str_starts_with($phone, '+') ? '+' : '';
        $digits = preg_replace('/\D+/', '', $phone);

        return $digits ? 'tel:'.$prefix.$digits : null;
    }
}
