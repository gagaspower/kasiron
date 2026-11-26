<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tambahkan kolom hanya jika belum ada agar migration bisa dilanjutkan
        // setelah gagal di tengah proses pada MySQL.
        if (!Schema::hasColumn('pembayaran_pembelian', 'supplier_id')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->ulid('supplier_id')->nullable()->after('nomor_pembayaran');
                $table
                    ->foreign('supplier_id', 'pembayaran_pembelian_supplier_fk')
                    ->references('id')
                    ->on('supplier')
                    ->restrictOnDelete();
            });
        }

        if (!Schema::hasColumn('pembayaran_pembelian', 'total_bayar')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->decimal('total_bayar', 20, 2)->nullable()->after('tanggal_pembayaran');
            });
        }

        if (
            Schema::hasColumn('pembayaran_pembelian', 'pembelian_id') &&
            Schema::hasColumn('pembayaran_pembelian', 'jumlah_bayar')
        ) {
            DB::table('pembayaran_pembelian')
                ->join('pembelian', 'pembayaran_pembelian.pembelian_id', '=', 'pembelian.id')
                ->update([
                    'pembayaran_pembelian.supplier_id' => DB::raw('pembelian.supplier_id'),
                    'pembayaran_pembelian.total_bayar' => DB::raw('pembayaran_pembelian.jumlah_bayar'),
                ]);
        }

        if (!Schema::hasTable('pembayaran_pembelian_detail')) {
            Schema::create('pembayaran_pembelian_detail', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->foreignUlid('pembayaran_pembelian_id');
                $table
                    ->foreign('pembayaran_pembelian_id', 'pp_detail_pembayaran_fk')
                    ->references('id')
                    ->on('pembayaran_pembelian')
                    ->cascadeOnDelete();
                $table->foreignUlid('pembelian_id');
                $table
                    ->foreign('pembelian_id', 'pp_detail_pembelian_fk')
                    ->references('id')
                    ->on('pembelian')
                    ->restrictOnDelete();
                $table->decimal('jumlah_bayar', 20, 2);
                $table->timestamp('dibuat_pada')->nullable();
                $table->timestamp('diperbarui_pada')->nullable();
                $table->unique(['pembayaran_pembelian_id', 'pembelian_id'], 'pembayaran_detail_unique_nota');
                $table->index('pembelian_id');
            });
        }

        // insertOrIgnore mencegah detail pembayaran terduplikasi jika proses
        // sebelumnya sempat berhenti ketika pemindahan data berlangsung.
        if (
            Schema::hasColumn('pembayaran_pembelian', 'pembelian_id') &&
            Schema::hasColumn('pembayaran_pembelian', 'jumlah_bayar')
        ) {
            DB::table('pembayaran_pembelian')->orderBy('id')->chunk(100, function ($payments) {
                foreach ($payments as $payment) {
                    DB::table('pembayaran_pembelian_detail')->insertOrIgnore([
                        'id'                      => (string) Illuminate\Support\Str::ulid(),
                        'pembayaran_pembelian_id' => $payment->id,
                        'pembelian_id'            => $payment->pembelian_id,
                        'jumlah_bayar'            => $payment->jumlah_bayar,
                        'dibuat_pada'             => $payment->dibuat_pada,
                        'diperbarui_pada'         => $payment->diperbarui_pada,
                    ]);
                }
            });

            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->dropForeign(['pembelian_id']);
                $table->dropIndex('pembayaran_pembelian_pembelian_id_tanggal_pembayaran_index');
                $table->dropColumn(['pembelian_id', 'jumlah_bayar']);
            });
        }

        // Pastikan data lama sudah terisi sebelum kolom diwajibkan.
        if (Schema::hasColumn('pembayaran_pembelian', 'total_bayar')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->decimal('total_bayar', 20, 2)->nullable(false)->change();
            });
        }

        if (Schema::hasTable('kelebihan_pembayaran_supplier') === false) {
            Schema::create('kelebihan_pembayaran_supplier', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->string('nomor_kelebihan', 40)->unique();
                $table->foreignUlid('pembayaran_pembelian_id')->unique();
                $table
                    ->foreign('pembayaran_pembelian_id', 'kelebihan_pembayaran_fk')
                    ->references('id')
                    ->on('pembayaran_pembelian')
                    ->restrictOnDelete();
                $table->foreignUlid('supplier_id');
                $table
                    ->foreign('supplier_id', 'kelebihan_supplier_fk')
                    ->references('id')
                    ->on('supplier')
                    ->restrictOnDelete();
                $table->decimal('jumlah_kelebihan', 20, 2);
                $table->date('tanggal');
                $table->text('catatan')->nullable();
                $table->foreignUlid('dibuat_oleh');
                $table
                    ->foreign('dibuat_oleh', 'kelebihan_pembuat_fk')
                    ->references('id')
                    ->on('users')
                    ->restrictOnDelete();
                $table->timestamp('dibuat_pada')->nullable();
                $table->timestamp('diperbarui_pada')->nullable();
                $table->index(['supplier_id', 'tanggal']);
            });
        }

        if (!Schema::hasTable('penyelesaian_kelebihan_supplier')) {
            Schema::create('penyelesaian_kelebihan_supplier', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->foreignUlid('kelebihan_pembayaran_supplier_id');
                $table
                    ->foreign('kelebihan_pembayaran_supplier_id', 'penyelesaian_kelebihan_fk')
                    ->references('id')
                    ->on('kelebihan_pembayaran_supplier')
                    ->restrictOnDelete();
                $table->string('jenis', 20);
                $table->decimal('jumlah', 20, 2);
                $table->date('tanggal');
                $table->string('referensi', 150)->nullable();
                $table->text('catatan')->nullable();
                $table->foreignUlid('dibuat_oleh');
                $table
                    ->foreign('dibuat_oleh', 'penyelesaian_pembuat_fk')
                    ->references('id')
                    ->on('users')
                    ->restrictOnDelete();
                $table->timestamp('dibuat_pada')->nullable();
                $table->timestamp('diperbarui_pada')->nullable();
                $table->index(['kelebihan_pembayaran_supplier_id', 'tanggal']);
            });
        }

        if (!Schema::hasTable('mutasi_saldo_supplier')) {
            Schema::create('mutasi_saldo_supplier', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->foreignUlid('supplier_id');
                $table
                    ->foreign('supplier_id', 'mutasi_supplier_fk')
                    ->references('id')
                    ->on('supplier')
                    ->restrictOnDelete();
                $table->foreignUlid('penyelesaian_kelebihan_supplier_id')->nullable();
                $table
                    ->foreign('penyelesaian_kelebihan_supplier_id', 'mutasi_penyelesaian_fk')
                    ->references('id')
                    ->on('penyelesaian_kelebihan_supplier')
                    ->restrictOnDelete();
                $table->string('jenis', 20);
                $table->decimal('jumlah', 20, 2);
                $table->date('tanggal');
                $table->string('referensi_tipe', 40)->nullable();
                $table->string('referensi_id', 40)->nullable();
                $table->text('catatan')->nullable();
                $table->foreignUlid('dibuat_oleh');
                $table
                    ->foreign('dibuat_oleh', 'mutasi_pembuat_fk')
                    ->references('id')
                    ->on('users')
                    ->restrictOnDelete();
                $table->timestamp('dibuat_pada')->nullable();
                $table->timestamp('diperbarui_pada')->nullable();
                $table->index(['supplier_id', 'tanggal']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_saldo_supplier');
        Schema::dropIfExists('penyelesaian_kelebihan_supplier');
        Schema::dropIfExists('kelebihan_pembayaran_supplier');
        Schema::dropIfExists('pembayaran_pembelian_detail');

        if (Schema::hasColumn('pembayaran_pembelian', 'supplier_id')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            });
        }

        if (Schema::hasColumn('pembayaran_pembelian', 'total_bayar')) {
            Schema::table('pembayaran_pembelian', function (Blueprint $table) {
                $table->dropColumn('total_bayar');
            });
        }
    }
};
