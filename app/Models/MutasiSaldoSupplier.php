<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['supplier_id', 'penyelesaian_kelebihan_supplier_id', 'jenis', 'jumlah', 'tanggal', 'referensi_tipe', 'referensi_id', 'catatan', 'dibuat_oleh'])]
class MutasiSaldoSupplier extends Model
{
    use HasUlids;

    protected $table = 'mutasi_saldo_supplier';

    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diperbarui_pada';

    public const JENIS_MASUK = 'masuk';

    public const JENIS_PEMAKAIAN = 'pemakaian';

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function penyelesaian(): BelongsTo
    {
        return $this->belongsTo(PenyelesaianKelebihanSupplier::class, 'penyelesaian_kelebihan_supplier_id');
    }

    protected function casts(): array
    {
        return ['jumlah' => 'decimal:2', 'tanggal' => 'date'];
    }
}
