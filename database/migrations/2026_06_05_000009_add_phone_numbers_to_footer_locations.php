<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->string('location_phone')->nullable()->after('location_postal_code');
        });

        Schema::table('footer_office_locations', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn('location_phone');
        });

        Schema::table('footer_office_locations', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
