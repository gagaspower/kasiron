<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pembayaran_pembelian', 'status')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->string('status', 20)->default('aktif')->after('catatan');
            });
        }

        if (!Schema::hasColumn('pembayaran_pembelian', 'alasan_pembatalan')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->text('alasan_pembatalan')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('pembayaran_pembelian', 'dibatalkan_oleh')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->ulid('dibatalkan_oleh')->nullable()->after('alasan_pembatalan');
                $table->foreign('dibatalkan_oleh', 'pembayaran_dibatalkan_user_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('pembayaran_pembelian', 'dibatalkan_pada')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->timestamp('dibatalkan_pada')->nullable()->after('dibatalkan_oleh');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pembayaran_pembelian', 'dibatalkan_oleh')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->dropForeign('pembayaran_dibatalkan_user_fk');
                $table->dropColumn('dibatalkan_oleh');
            });
        }

        foreach (['dibatalkan_pada', 'alasan_pembatalan', 'status'] as $column) {
            if (Schema::hasColumn('pembayaran_pembelian', $column)) {
                Schema::table('pembayaran_pembelian', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
