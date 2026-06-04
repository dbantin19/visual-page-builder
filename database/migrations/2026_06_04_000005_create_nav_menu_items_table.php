<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('url')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('nav_menu_items')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_menu_items');
    }
};
