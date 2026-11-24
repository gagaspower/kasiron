<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('kategori_id')
                ->nullable()
                ->constrained('kategori')
                ->restrictOnDelete();

            $table->foreignUlid('satuan_dasar_id')
                ->constrained('satuan')
                ->restrictOnDelete();

            $table->string('kode_sku')->nullable()->unique();
            $table->string('nama');

            $table->decimal('jumlah_stok', 20, 6)->default(0);
            $table->decimal('stok_minimum', 20, 6)->default(0);

            $table->boolean('aktif')->default(true);

            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
