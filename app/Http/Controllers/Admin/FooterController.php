<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterCoupon;
use App\Models\FooterOfficeLocation;
use App\Models\FooterSetting;
use App\Support\ContentUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FooterController extends Controller
{
    public function index()
    {
        $footerSetting = FooterSetting::get();
        $coupons = FooterCoupon::ordered()->get();
        $officeLocations = FooterOfficeLocation::ordered()->get();

        return view('admin.footer.index', compact('footerSetting', 'coupons', 'officeLocations'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'coupons_enabled' => 'nullable|boolean',
            'location_enabled' => 'nullable|boolean',
            'affiliations_enabled' => 'nullable|boolean',
            'location_name' => 'nullable|string|max:120',
            'location_address_line_1' => 'nullable|string|max:160',
            'location_address_line_2' => 'nullable|string|max:160',
            'location_city' => 'nullable|string|max:100',
            'location_region' => 'nullable|string|max:80',
            'location_postal_code' => 'nullable|string|max:30',
            'location_phone' => 'nullable|string|max:40',
            'affiliation_badge' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,avif|max:4096',
            'affiliation_badge_path' => 'nullable|string|max:255',
            'affiliation_badge_alt' => 'nullable|string|max:120',
            'affiliation_link_url' => 'nullable|string|max:2048|not_regex:/^\s*javascript:/i',
            'affiliation_badge_remove' => 'nullable|boolean',
            'coupons' => 'nullable|array',
            'coupons.*' => 'array',
            'coupons.*.kicker' => 'nullable|string|max:80',
            'coupons.*.headline' => 'nullable|string|max:120',
            'coupons.*.description' => 'nullable|string|max:160',
            'coupons.*.fine_print' => 'nullable|string|max:300',
            'coupons.*.expires_enabled' => 'nullable|boolean',
            'coupons.*.expires_end_of_month' => 'nullable|boolean',
            'coupons.*.expires_at' => 'nullable|date',
            'office_locations' => 'nullable|array',
            'office_locations.*' => 'array',
            'office_locations.*.name' => 'nullable|string|max:120',
            'office_locations.*.address_line_1' => 'nullable|string|max:160',
            'office_locations.*.address_line_2' => 'nullable|string|max:160',
            'office_locations.*.city' => 'nullable|string|max:100',
            'office_locations.*.region' => 'nullable|string|max:80',
            'office_locations.*.postal_code' => 'nullable|string|max:30',
            'office_locations.*.phone' => 'nullable|string|max:40',
            'office_locations.*.link_url' => 'nullable|string|max:2048|not_regex:/^\s*javascript:/i',
            'section_order' => 'nullable|array',
            'section_order.*' => 'string|in:main_location,office_locations,affiliations,coupons',
            'section_alignments' => 'nullable|array',
            'section_alignments.*' => 'string|in:left,center,right',
            'section_content_alignments' => 'nullable|array',
            'section_content_alignments.*' => 'string|in:left,center,right',
        ]);

        $couponsEnabled = $request->boolean('coupons_enabled');
        $locationEnabled = $request->boolean('location_enabled');
        $affiliationsEnabled = $request->boolean('affiliations_enabled');
        $locationData = $this->cleanLocationData($request);
        $affiliationData = $this->cleanAffiliationData($request);
        $couponRows = $this->cleanCouponRows($request->input('coupons', []));
        $officeLocationRows = $this->cleanOfficeLocationRows($request->input('office_locations', []));
        $sectionOrder = $this->cleanSectionOrder($request->input('section_order', []));
        $sectionAlignments = $this->cleanSectionAlignments($request->input('section_alignments', []));
        $sectionContentAlignments = $this->cleanSectionContentAlignments($request->input('section_content_alignments', []));

        if ($couponsEnabled && $couponRows->isEmpty()) {
            throw ValidationException::withMessages([
                'coupons' => 'Add at least one coupon before enabling coupons in the footer.',
            ]);
        }

        if ($locationEnabled && blank($locationData['location_address_line_1'])) {
            throw ValidationException::withMessages([
                'location_address_line_1' => 'Add a street address before enabling the footer location.',
            ]);
        }

        if ($affiliationsEnabled && blank($affiliationData['affiliation_badge_path'])) {
            throw ValidationException::withMessages([
                'affiliation_badge' => 'Upload an affiliation badge before enabling affiliations in the footer.',
            ]);
        }

        $couponRows->each(function (array $row, int $index): void {
            if (blank($row['headline'])) {
                throw ValidationException::withMessages([
                    "coupons.$index.headline" => 'Each coupon needs an offer headline.',
                ]);
            }

            if ($row['expires_enabled'] && ! $row['expires_end_of_month'] && blank($row['expires_at'])) {
                throw ValidationException::withMessages([
                    "coupons.$index.expires_at" => 'Choose a custom expiry date or use the end of the current month.',
                ]);
            }
        });

        $officeLocationRows->each(function (array $row, int $index): void {
            if (blank($row['address_line_1'])) {
                throw ValidationException::withMessages([
                    "office_locations.$index.address_line_1" => 'Each office location needs a street address.',
                ]);
            }
        });

        DB::transaction(function () use ($couponsEnabled, $locationEnabled, $affiliationsEnabled, $locationData, $affiliationData, $couponRows, $officeLocationRows, $sectionOrder, $sectionAlignments, $sectionContentAlignments) {
            FooterSetting::get()->update([
                'coupons_enabled' => $couponsEnabled,
                'location_enabled' => $locationEnabled,
                'affiliations_enabled' => $affiliationsEnabled,
                'section_order' => $sectionOrder,
                'section_alignments' => $sectionAlignments,
                'section_content_alignments' => $sectionContentAlignments,
                ...$locationData,
                ...$affiliationData,
            ]);

            FooterCoupon::query()->delete();
            FooterOfficeLocation::query()->delete();

            $couponRows->values()->each(function (array $row, int $index): void {
                FooterCoupon::create([
                    'kicker' => $row['kicker'],
                    'headline' => $row['headline'],
                    'description' => $row['description'],
                    'fine_print' => $row['fine_print'],
                    'expires_enabled' => $row['expires_enabled'],
                    'expires_end_of_month' => $row['expires_end_of_month'],
                    'expires_at' => $row['expires_at'],
                    'sort_order' => $index,
                ]);
            });

            $officeLocationRows->values()->each(function (array $row, int $index): void {
                FooterOfficeLocation::create([
                    'name' => $row['name'],
                    'address_line_1' => $row['address_line_1'],
                    'address_line_2' => $row['address_line_2'],
                    'city' => $row['city'],
                    'region' => $row['region'],
                    'postal_code' => $row['postal_code'],
                    'phone' => $row['phone'],
                    'link_url' => $row['link_url'],
                    'sort_order' => $index,
                ]);
            });
        });

        return redirect()->route('admin.footer.index')->with('success', 'Footer saved.');
    }

    private function cleanLocationData(Request $request): array
    {
        return [
            'location_name' => trim($request->input('location_name', '')),
            'location_address_line_1' => trim($request->input('location_address_line_1', '')),
            'location_address_line_2' => trim($request->input('location_address_line_2', '')),
            'location_city' => trim($request->input('location_city', '')),
            'location_region' => trim($request->input('location_region', '')),
            'location_postal_code' => trim($request->input('location_postal_code', '')),
            'location_phone' => trim($request->input('location_phone', '')),
        ];
    }

    private function cleanAffiliationData(Request $request): array
    {
        $badgePath = $request->boolean('affiliation_badge_remove')
            ? ''
            : $this->cleanBadgePath($request->input('affiliation_badge_path', ''));

        if ($request->hasFile('affiliation_badge')) {
            $upload = ContentUploads::store($request->file('affiliation_badge'));
            $badgePath = $upload['name'];
        }

        return [
            'affiliation_badge_path' => $badgePath ?: null,
            'affiliation_badge_alt' => trim($request->input('affiliation_badge_alt', '')),
            'affiliation_link_url' => trim($request->input('affiliation_link_url', '')),
        ];
    }

    private function cleanCouponRows(array $rows)
    {
        return collect($rows)
            ->map(function (array $row) {
                $expiresEnabled = filter_var($row['expires_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $expiresEndOfMonth = $expiresEnabled
                    ? filter_var($row['expires_end_of_month'] ?? false, FILTER_VALIDATE_BOOLEAN)
                    : false;

                return [
                    'kicker' => trim($row['kicker'] ?? ''),
                    'headline' => trim($row['headline'] ?? ''),
                    'description' => trim($row['description'] ?? ''),
                    'fine_print' => trim($row['fine_print'] ?? ''),
                    'expires_enabled' => $expiresEnabled,
                    'expires_end_of_month' => $expiresEndOfMonth,
                    'expires_at' => $expiresEnabled && ! $expiresEndOfMonth ? trim($row['expires_at'] ?? '') : null,
                ];
            })
            ->filter(fn(array $row) => $row['expires_enabled'] || collect([
                $row['kicker'],
                $row['headline'],
                $row['description'],
                $row['fine_print'],
            ])->contains(fn(string $value) => $value !== ''))
            ->values();
    }

    private function cleanOfficeLocationRows(array $rows)
    {
        return collect($rows)
            ->map(fn(array $row) => [
                'name' => trim($row['name'] ?? ''),
                'address_line_1' => trim($row['address_line_1'] ?? ''),
                'address_line_2' => trim($row['address_line_2'] ?? ''),
                'city' => trim($row['city'] ?? ''),
                'region' => trim($row['region'] ?? ''),
                'postal_code' => trim($row['postal_code'] ?? ''),
                'phone' => trim($row['phone'] ?? ''),
                'link_url' => trim($row['link_url'] ?? ''),
            ])
            ->filter(fn(array $row) => collect($row)->contains(fn(string $value) => $value !== ''))
            ->values();
    }

    private function cleanBadgePath(?string $path): string
    {
        $path = trim((string) $path);
        $safeName = basename(str_replace('\\', '/', $path));
        $extension = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));

        return $safeName === $path && in_array($extension, ContentUploads::IMAGE_EXTENSIONS, true)
            ? $safeName
            : '';
    }

    private function cleanSectionOrder(array $sectionOrder): array
    {
        return collect($sectionOrder)
            ->filter(fn($section) => is_string($section) && in_array($section, FooterSetting::DEFAULT_SECTION_ORDER, true))
            ->unique()
            ->merge(collect(FooterSetting::DEFAULT_SECTION_ORDER)->diff($sectionOrder))
            ->values()
            ->all();
    }

    private function cleanSectionAlignments(array $sectionAlignments): array
    {
        return $this->cleanAlignmentMap($sectionAlignments, FooterSetting::DEFAULT_SECTION_ALIGNMENTS);
    }

    private function cleanSectionContentAlignments(array $sectionContentAlignments): array
    {
        return $this->cleanAlignmentMap(
            $sectionContentAlignments,
            FooterSetting::DEFAULT_SECTION_CONTENT_ALIGNMENTS
        );
    }

    private function cleanAlignmentMap(array $alignments, array $defaults): array
    {
        return collect($defaults)
            ->map(function (string $default, string $section) use ($alignments) {
                $alignment = $alignments[$section] ?? $default;

                return is_string($alignment) && in_array($alignment, FooterSetting::ALIGNMENTS, true)
                    ? $alignment
                    : $default;
            })
            ->all();
    }
}
