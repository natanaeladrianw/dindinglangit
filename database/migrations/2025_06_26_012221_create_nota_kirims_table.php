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
        Schema::table('nota_kirims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->cascadeOnDelete();
            $table->foreignId('satuan_id')->constrained('satuans')->cascadeOnDelete();
            $table->foreignId('stok_dapur_id')->constrained('stok_dapurs')->cascadeOnDelete();
            $table->float('jumlah');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_kirims');
    }
};
