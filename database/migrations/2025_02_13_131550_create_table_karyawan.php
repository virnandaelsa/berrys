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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id('id_karyawan');
            $table->string('nik', 16)->unique()->nullable();
            $table->string('nama', 255)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->enum('jen_kel', ['L', 'P'])->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->string('role', 50)->nullable();
            $table->string('username', 50)->nullable();
            $table->string('password', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
