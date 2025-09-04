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
        Schema::table('penggunaan_bahan_bakus', function (Blueprint $table) {
            $table->unsignedBigInteger('bahan_baku_id');
            $table->unsignedBigInteger('satuan_id');
            $table->foreign('bahan_baku_id')->references('id')->on('bahan_bakus')->onDelete('cascade');
            $table->foreign('satuan_id')->references('id')->on('satuans')->onDelete('cascade');
            $table->integer('jumlah_pakai');
            $table->integer('sisa_fisik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggunaan_bahan_bakus', function (Blueprint $table) {
            //
        });
    }
};
