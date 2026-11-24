<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satuan_produk', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('produk_id')
                ->constrained('produk')
                ->cascadeOnDelete();

            $table->foreignUlid('satuan_id')
                ->constrained('satuan')
                ->restrictOnDelete();

            $table->decimal('konversi_ke_satuan_dasar', 20, 6);
            $table->unsignedTinyInteger('presisi_kuantitas')->default(0);

            $table->unsignedBigInteger('harga_jual');

            $table->string('barcode')->nullable()->unique();

            $table->boolean('satuan_utama')->default(false);
            $table->boolean('aktif')->default(true);

            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();

            $table->softDeletes();

            $table->unique(['produk_id', 'satuan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satuan_produk');
    }
};
