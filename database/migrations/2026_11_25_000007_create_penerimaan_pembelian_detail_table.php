<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_pembelian_detail', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('penerimaan_pembelian_id')
                ->constrained('penerimaan_pembelian')
                ->cascadeOnDelete();

            $table->foreignUlid('pembelian_detail_id')
                ->constrained('pembelian_detail')
                ->restrictOnDelete();

            $table->decimal('jumlah_diterima', 20, 6);
            $table->decimal('jumlah_diterima_dalam_satuan_dasar', 20, 6);
            $table->text('catatan')->nullable();

            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index('pembelian_detail_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_pembelian_detail');
    }
};
