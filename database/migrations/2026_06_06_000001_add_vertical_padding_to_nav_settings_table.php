<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nav_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('nav_settings', 'vertical_padding')) {
                $table->string('vertical_padding', 20)->default('standard')->after('logo_position');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nav_settings', function (Blueprint $table) {
            if (Schema::hasColumn('nav_settings', 'vertical_padding')) {
                $table->dropColumn('vertical_padding');
            }
        });
    }
};
