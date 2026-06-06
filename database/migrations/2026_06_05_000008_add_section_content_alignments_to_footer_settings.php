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
            $table->json('section_content_alignments')->nullable()->after('section_alignments');
        });

        DB::table('footer_settings')->get()->each(function ($footerSetting): void {
            $sectionAlignments = json_decode($footerSetting->section_alignments ?? '[]', true);
            $sectionAlignments = is_array($sectionAlignments)
                ? $sectionAlignments
                : FooterSetting::DEFAULT_SECTION_CONTENT_ALIGNMENTS;

            $contentAlignments = collect(FooterSetting::DEFAULT_SECTION_CONTENT_ALIGNMENTS)
                ->map(function (string $default, string $section) use ($sectionAlignments) {
                    $alignment = $sectionAlignments[$section] ?? $default;

                    return in_array($alignment, FooterSetting::ALIGNMENTS, true) ? $alignment : $default;
                })
                ->all();

            DB::table('footer_settings')
                ->where('id', $footerSetting->id)
                ->update([
                    'section_content_alignments' => json_encode($contentAlignments),
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn('section_content_alignments');
        });
    }
};
