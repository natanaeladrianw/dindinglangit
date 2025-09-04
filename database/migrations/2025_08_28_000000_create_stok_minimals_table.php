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
        Schema::create('stok_minimals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('cascade');
            $table->foreignId('satuan_id')->constrained('satuans')->onDelete('cascade');
            $table->enum('lokasi', ['dapur', 'gudang']);
            $table->unsignedBigInteger('jumlah');
            $table->timestamps();
            $table->unique(['bahan_baku_id', 'lokasi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_minimals');
    }
};


