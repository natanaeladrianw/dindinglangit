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
        Schema::create('serah_terima_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_kasir_id')->constrained('shift_kasirs')->onDelete('cascade');
            $table->integer('status')->nullable();
            $table->string('serah');
            $table->string('terima');
            $table->dateTime('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serah_terima_stoks');
    }
};
