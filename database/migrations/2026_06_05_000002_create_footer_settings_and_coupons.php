<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('coupons_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('footer_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('kicker')->nullable();
            $table->string('headline');
            $table->string('description')->nullable();
            $table->text('fine_print')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('footer_settings')->insert([
            'coupons_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_coupons');
        Schema::dropIfExists('footer_settings');
    }
};
