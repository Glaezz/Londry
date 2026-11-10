<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('detail_transaksi')]
#[Fillable(['transaksi_id', 'layanan_id', 'jumlah_unit', 'harga_satuan', 'subtotal'])]
class DetailTransaksi extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah_unit' => 'decimal:2',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Transaksi, $this>
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    /**
     * @return BelongsTo<Layanan, $this>
     */
    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }
}
