<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran_pembelian', function (Blueprint $table) {
            $table->foreignUlid('supplier_id')->nullable()->after('nomor_pembayaran')->constrained('supplier')->restrictOnDelete();
            $table->decimal('total_bayar', 20, 2)->nullable()->after('tanggal_pembayaran');
        });

        DB::table('pembayaran_pembelian')
            ->join('pembelian', 'pembayaran_pembelian.pembelian_id', '=', 'pembelian.id')
            ->update([
                'pembayaran_pembelian.supplier_id' => DB::raw('pembelian.supplier_id'),
                'pembayaran_pembelian.total_bayar' => DB::raw('pembayaran_pembelian.jumlah_bayar'),
            ]);

        Schema::create('pembayaran_pembelian_detail', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('pembayaran_pembelian_id')->constrained('pembayaran_pembelian')->cascadeOnDelete();
            $table->foreignUlid('pembelian_id')->constrained('pembelian')->restrictOnDelete();
            $table->decimal('jumlah_bayar', 20, 2);
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->unique(['pembayaran_pembelian_id', 'pembelian_id'], 'pembayaran_detail_unique_nota');
            $table->index('pembelian_id');
        });

        DB::table('pembayaran_pembelian')->orderBy('id')->chunk(100, function ($payments) {
            foreach ($payments as $payment) {
                DB::table('pembayaran_pembelian_detail')->insert([
                    'id' => (string) Illuminate\Support\Str::ulid(),
                    'pembayaran_pembelian_id' => $payment->id,
                    'pembelian_id' => $payment->pembelian_id,
                    'jumlah_bayar' => $payment->jumlah_bayar,
                    'dibuat_pada' => $payment->dibuat_pada,
                    'diperbarui_pada' => $payment->diperbarui_pada,
                ]);
            }
        });

        Schema::table('pembayaran_pembelian', function (Blueprint $table) {
            $table->dropForeign(['pembelian_id']);
            $table->dropIndex('pembayaran_pembelian_pembelian_id_tanggal_pembayaran_index');
            $table->dropColumn(['pembelian_id', 'jumlah_bayar']);
            $table->decimal('total_bayar', 20, 2)->nullable(false)->change();
            $table->foreignUlid('supplier_id')->nullable(false)->change();
        });

        Schema::create('kelebihan_pembayaran_supplier', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nomor_kelebihan', 40)->unique();
            $table->foreignUlid('pembayaran_pembelian_id')->unique()->constrained('pembayaran_pembelian')->restrictOnDelete();
            $table->foreignUlid('supplier_id')->constrained('supplier')->restrictOnDelete();
            $table->decimal('jumlah_kelebihan', 20, 2);
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->foreignUlid('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->index(['supplier_id', 'tanggal']);
        });

        Schema::create('penyelesaian_kelebihan_supplier', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('kelebihan_pembayaran_supplier_id')->constrained('kelebihan_pembayaran_supplier')->restrictOnDelete();
            $table->string('jenis', 20);
            $table->decimal('jumlah', 20, 2);
            $table->date('tanggal');
            $table->string('referensi', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignUlid('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->index(['kelebihan_pembayaran_supplier_id', 'tanggal']);
        });

        Schema::create('mutasi_saldo_supplier', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('supplier_id')->constrained('supplier')->restrictOnDelete();
            $table->foreignUlid('penyelesaian_kelebihan_supplier_id')->nullable()->constrained('penyelesaian_kelebihan_supplier')->restrictOnDelete();
            $table->string('jenis', 20);
            $table->decimal('jumlah', 20, 2);
            $table->date('tanggal');
            $table->string('referensi_tipe', 40)->nullable();
            $table->string('referensi_id', 40)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignUlid('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->index(['supplier_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_saldo_supplier');
        Schema::dropIfExists('penyelesaian_kelebihan_supplier');
        Schema::dropIfExists('kelebihan_pembayaran_supplier');
        Schema::dropIfExists('pembayaran_pembelian_detail');

        Schema::table('pembayaran_pembelian', function (Blueprint $table) {
            $table->foreignUlid('pembelian_id')->nullable()->after('nomor_pembayaran')->constrained('pembelian')->restrictOnDelete();
            $table->decimal('jumlah_bayar', 20, 2)->nullable();
        });

        DB::table('pembayaran_pembelian_detail')->orderBy('id')->get()->each(function ($detail) {
            DB::table('pembayaran_pembelian')->where('id', $detail->pembayaran_pembelian_id)->update([
                'pembelian_id' => $detail->pembelian_id,
                'jumlah_bayar' => $detail->jumlah_bayar,
            ]);
        });

        Schema::table('pembayaran_pembelian', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'total_bayar']);
            $table->index(['pembelian_id', 'tanggal_pembayaran']);
        });
    }
};