<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_stok', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('produk_id')
                ->constrained('produk')
                ->restrictOnDelete();

            $table->string('jenis', 30);
            $table->decimal('jumlah', 20, 6);
            $table->decimal('stok_sebelum', 20, 6);
            $table->decimal('stok_sesudah', 20, 6);

            $table->string('referensi_tipe', 50)->nullable();
            $table->string('referensi_id', 26)->nullable();
            $table->text('catatan')->nullable();

            $table->timestamp('dibuat_pada')->useCurrent();

            $table->index(['produk_id', 'dibuat_pada']);
            $table->index(['referensi_tipe', 'referensi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_stok');
    }
};
