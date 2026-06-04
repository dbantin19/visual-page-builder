<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nav_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('nav_settings', 'logo_position')) {
                $table->string('logo_position', 10)->default('left')->after('alignment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nav_settings', function (Blueprint $table) {
            if (Schema::hasColumn('nav_settings', 'logo_position')) {
                $table->dropColumn('logo_position');
            }
        });
    }
};
