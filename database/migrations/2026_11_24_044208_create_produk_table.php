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
        Schema::create('produk', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('kategori_id')->nullable()->constrained('kategori')->restrictOnDelete();
            $table->string('kode_sku')->nullable()->unique();
            $table->string('nama');
            $table->string('satuan_dasar', 10);
            $table->unsignedBigInteger('jumlah_stok')->default(0);
            $table->unsignedBigInteger('stok_minimum')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
