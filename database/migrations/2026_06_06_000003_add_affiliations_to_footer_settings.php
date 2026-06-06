<?php

use App\Models\FooterSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->boolean('affiliations_enabled')->default(false)->after('location_enabled');
            $table->string('affiliation_badge_path')->nullable()->after('location_phone');
            $table->string('affiliation_badge_alt', 120)->nullable()->after('affiliation_badge_path');
            $table->string('affiliation_link_url', 2048)->nullable()->after('affiliation_badge_alt');
        });

        DB::table('footer_settings')->get()->each(function ($footerSetting): void {
            $order = json_decode($footerSetting->section_order ?? '[]', true);
            $order = is_array($order) ? $order : [];

            if (! in_array('affiliations', $order, true)) {
                $couponIndex = array_search('coupons', $order, true);
                if ($couponIndex === false) {
                    $order[] = 'affiliations';
                } else {
                    array_splice($order, $couponIndex, 0, ['affiliations']);
                }
            }

            $order = collect($order)
                ->filter(fn($section) => is_string($section) && in_array($section, FooterSetting::DEFAULT_SECTION_ORDER, true))
                ->unique()
                ->merge(collect(FooterSetting::DEFAULT_SECTION_ORDER)->diff($order))
                ->values()
                ->all();

            $sectionAlignments = $this->withAffiliationAlignment($footerSetting->section_alignments);
            $sectionContentAlignments = $this->withAffiliationAlignment($footerSetting->section_content_alignments);

            DB::table('footer_settings')
                ->where('id', $footerSetting->id)
                ->update([
                    'section_order' => json_encode($order),
                    'section_alignments' => json_encode($sectionAlignments),
                    'section_content_alignments' => json_encode($sectionContentAlignments),
                ]);
        });
    }

    public function down(): void
    {
        DB::table('footer_settings')->get()->each(function ($footerSetting): void {
            $order = json_decode($footerSetting->section_order ?? '[]', true);
            $order = collect(is_array($order) ? $order : [])
                ->reject(fn($section) => $section === 'affiliations')
                ->values()
                ->all();

            $sectionAlignments = $this->withoutAffiliationAlignment($footerSetting->section_alignments);
            $sectionContentAlignments = $this->withoutAffiliationAlignment($footerSetting->section_content_alignments);

            DB::table('footer_settings')
                ->where('id', $footerSetting->id)
                ->update([
                    'section_order' => json_encode($order),
                    'section_alignments' => json_encode($sectionAlignments),
                    'section_content_alignments' => json_encode($sectionContentAlignments),
                ]);
        });

        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn([
                'affiliations_enabled',
                'affiliation_badge_path',
                'affiliation_badge_alt',
                'affiliation_link_url',
            ]);
        });
    }

    private function withAffiliationAlignment(?string $json): array
    {
        $alignments = json_decode($json ?? '[]', true);
        $alignments = is_array($alignments) ? $alignments : [];

        return collect(FooterSetting::DEFAULT_SECTION_ALIGNMENTS)
            ->map(function (string $default, string $section) use ($alignments) {
                $alignment = $alignments[$section] ?? $default;

                return in_array($alignment, FooterSetting::ALIGNMENTS, true) ? $alignment : $default;
            })
            ->all();
    }

    private function withoutAffiliationAlignment(?string $json): array
    {
        $alignments = json_decode($json ?? '[]', true);
        $alignments = is_array($alignments) ? $alignments : [];
        unset($alignments['affiliations']);

        return $alignments;
    }
};
