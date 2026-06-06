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
            $table->json('section_alignments')->nullable()->after('section_order');
        });

        DB::table('footer_settings')->update([
            'section_alignments' => json_encode(FooterSetting::DEFAULT_SECTION_ALIGNMENTS),
        ]);
    }

    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn('section_alignments');
        });
    }
};
