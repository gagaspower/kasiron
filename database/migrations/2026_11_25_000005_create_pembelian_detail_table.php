<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_detail', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('pembelian_id')
                ->constrained('pembelian')
                ->cascadeOnDelete();

            $table->foreignUlid('produk_id')
                ->constrained('produk')
                ->restrictOnDelete();

            $table->foreignUlid('satuan_produk_id')
                ->constrained('satuan_produk')
                ->restrictOnDelete();

            // Snapshot agar histori transaksi tidak berubah ketika master produk/satuan diubah.
            $table->string('nama_produk');
            $table->string('kode_sku', 100)->nullable();
            $table->string('nama_satuan', 100);
            $table->decimal('konversi_ke_satuan_dasar', 20, 6);

            $table->decimal('jumlah', 20, 6);
            $table->decimal('jumlah_dalam_satuan_dasar', 20, 6);
            $table->decimal('harga_beli', 20, 2);
            $table->decimal('diskon', 20, 2)->default(0);
            $table->decimal('subtotal', 20, 2);
            $table->text('catatan')->nullable();

            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['pembelian_id', 'produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_detail');
    }
};
