<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nomor_pembelian', 40)->unique();

            $table->foreignUlid('supplier_id')
                ->constrained('supplier')
                ->restrictOnDelete();

            $table->string('nomor_faktur_supplier', 100)->nullable();
            $table->date('tanggal_pembelian');
            $table->date('tanggal_jatuh_tempo')->nullable();

            $table->string('status', 30)->default('draft');
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->decimal('diskon', 20, 2)->default(0);
            $table->decimal('biaya_lain', 20, 2)->default(0);
            $table->decimal('total', 20, 2)->default(0);

            $table->text('catatan')->nullable();

            $table->foreignUlid('dibuat_oleh')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignUlid('disetujui_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->index(['status', 'tanggal_pembelian']);
            $table->index(['supplier_id', 'tanggal_pembelian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};
