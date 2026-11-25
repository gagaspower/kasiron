<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_pembelian', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nomor_penerimaan', 40)->unique();

            $table->foreignUlid('pembelian_id')
                ->constrained('pembelian')
                ->restrictOnDelete();

            $table->date('tanggal_penerimaan');
            $table->string('status', 20)->default('draft');
            $table->text('catatan')->nullable();

            $table->foreignUlid('dibuat_oleh')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignUlid('diterima_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('diterima_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['pembelian_id', 'status']);
            $table->index('tanggal_penerimaan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_pembelian');
    }
};
