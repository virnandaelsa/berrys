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
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->string('hari', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('shift', 50)->nullable();
            $table->string('tempat', 50)->nullable();
            $table->unsignedBigInteger('id_karyawan')->nullable();
            $table->timestamps();

            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
