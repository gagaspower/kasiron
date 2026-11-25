<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('nomor_telepon', 30)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->softDeletes();
            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};
