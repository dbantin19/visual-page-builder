<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->boolean('location_enabled')->default(false)->after('coupons_enabled');
            $table->string('location_name')->nullable()->after('location_enabled');
            $table->string('location_address_line_1')->nullable()->after('location_name');
            $table->string('location_address_line_2')->nullable()->after('location_address_line_1');
            $table->string('location_city')->nullable()->after('location_address_line_2');
            $table->string('location_region')->nullable()->after('location_city');
            $table->string('location_postal_code')->nullable()->after('location_region');
        });
    }

    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn([
                'location_enabled',
                'location_name',
                'location_address_line_1',
                'location_address_line_2',
                'location_city',
                'location_region',
                'location_postal_code',
            ]);
        });
    }
};
