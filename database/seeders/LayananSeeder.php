<?php

namespace Database\Seeders;

use App\Enums\JenisLayanan;
use App\Models\Layanan;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    /**
     * Seed the application's services.
     */
    public function run(): void
    {
        $layanan = [
            ['nama_layanan' => 'Cuci Komplit Kiloan', 'jenis' => JenisLayanan::Kiloan, 'harga_per_unit' => 7000],
            ['nama_layanan' => 'Setrika Saja', 'jenis' => JenisLayanan::Kiloan, 'harga_per_unit' => 5000],
            ['nama_layanan' => 'Cuci Bedcover', 'jenis' => JenisLayanan::Satuan, 'harga_per_unit' => 25000],
        ];

        foreach ($layanan as $item) {
            Layanan::updateOrCreate(
                ['nama_layanan' => $item['nama_layanan']],
                ['jenis' => $item['jenis'], 'harga_per_unit' => $item['harga_per_unit']],
            );
        }
    }
}
