<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bahan_baku_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bahan_baku_id');
            $table->unsignedBigInteger('menu_id');
            $table->foreign('bahan_baku_id')->references('id')->on('bahan_bakus')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_baku_menus');
    }
};
