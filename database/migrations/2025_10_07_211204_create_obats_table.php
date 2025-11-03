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
        Schema::create('obats', function (Blueprint $table) { // Laravel otomatis membuat nama tabel jamak
            $table->id('idObat');
            $table->string('namaObat', 100);
            $table->string('jenisObat', 50);
            $table->string('kategori', 50)->nullable();
            $table->decimal('harga', 10, 2);
            $table->integer('stok');
            $table->text('keterangan')->nullable();
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obats');
    }
};
