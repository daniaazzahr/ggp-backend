<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('konsultasionline', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('namaid')->nullable();
            $table->string('kota')->nullable();
            $table->string('alamat')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('email');
            $table->string('telepon');
            $table->string('kategori')->nullable();
            $table->text('buktiTransaksi');
            $table->string('pesanKonsultasi', 7000);
            $table->string('jenisClient');
            $table->string('namaPerusahaan')->nullable();
            $table->string('advokat')->nullable()->default('Belum ada Advokat');
            $table->string('status')->default('Menunggu Verifikasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('konsultasionline');
    }
};
