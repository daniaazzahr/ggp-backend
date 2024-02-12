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
        Schema::create('klinikhukum', function (Blueprint $table) {
            $table->id();
            $table->string('pertanyaan', 5000);
            $table->string('jawaban', 7000)->default('');
            $table->string('kategori');
            $table->string('penulis');
            $table->string('penulisid')->nullable();
            $table->string('isAnswer')->default(false);
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
        Schema::dropIfExists('klinikhukum');
    }
};
