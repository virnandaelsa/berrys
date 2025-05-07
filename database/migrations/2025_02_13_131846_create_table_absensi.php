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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id('id_absensi');
            $table->unsignedBigInteger('id_karyawan')->nullable();
            $table->string('foto', 255)->nullable();
            $table->date('tanggal')->nullable();
            $table->time('jam_kerja')->nullable();
            $table->unsignedBigInteger('id_jadwal')->nullable();
            $table->timestamps();

            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawan')->onDelete('cascade');
            $table->foreign('id_jadwal')->references('id_jadwal')->on('jadwal')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
