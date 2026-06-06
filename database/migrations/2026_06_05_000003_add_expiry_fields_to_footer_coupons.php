<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_coupons', function (Blueprint $table) {
            $table->boolean('expires_enabled')->default(false)->after('fine_print');
            $table->boolean('expires_end_of_month')->default(false)->after('expires_enabled');
            $table->date('expires_at')->nullable()->after('expires_end_of_month');
        });
    }

    public function down(): void
    {
        Schema::table('footer_coupons', function (Blueprint $table) {
            $table->dropColumn(['expires_enabled', 'expires_end_of_month', 'expires_at']);
        });
    }
};
