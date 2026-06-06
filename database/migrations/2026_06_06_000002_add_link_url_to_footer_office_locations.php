<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_office_locations', function (Blueprint $table) {
            $table->string('link_url', 2048)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('footer_office_locations', function (Blueprint $table) {
            $table->dropColumn('link_url');
        });
    }
};
