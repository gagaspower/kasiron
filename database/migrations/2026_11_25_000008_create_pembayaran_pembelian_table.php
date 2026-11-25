<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_pembelian', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nomor_pembayaran', 40)->unique();

            $table->foreignUlid('pembelian_id')
                ->constrained('pembelian')
                ->restrictOnDelete();

            $table->date('tanggal_pembayaran');
            $table->decimal('jumlah_bayar', 20, 2);
            $table->string('metode_pembayaran', 30);
            $table->string('referensi_pembayaran', 150)->nullable();
            $table->text('catatan')->nullable();

            $table->foreignUlid('dibuat_oleh')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['pembelian_id', 'tanggal_pembayaran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_pembelian');
    }
};
