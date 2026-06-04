<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_settings', function (Blueprint $table) {
            $table->id();
            $table->string('alignment', 10)->default('left');
            $table->timestamps();
        });

        DB::table('nav_settings')->insert(['alignment' => 'left']);
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_settings');
    }
};
