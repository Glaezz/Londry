<?php

namespace App\Models;

use App\Enums\JenisLayanan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('layanan')]
#[Fillable(['nama_layanan', 'jenis', 'harga_per_unit'])]
class Layanan extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis' => JenisLayanan::class,
            'harga_per_unit' => 'decimal:2',
        ];
    }

    /**
     * @return HasMany<DetailTransaksi, $this>
     */
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'layanan_id');
    }
}
