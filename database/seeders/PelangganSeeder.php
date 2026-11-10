<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    /**
     * Seed the application's customers.
     */
    public function run(): void
    {
        $pelanggan = [
            ['nama' => 'Budi Santoso', 'no_hp' => '081234567001', 'alamat' => 'Jl. Merdeka No. 10'],
            ['nama' => 'Siti Aminah', 'no_hp' => '081234567002', 'alamat' => 'Jl. Sudirman No. 21'],
            ['nama' => 'Agus Wijaya', 'no_hp' => '081234567003', 'alamat' => 'Jl. Diponegoro No. 5'],
            ['nama' => 'Dewi Lestari', 'no_hp' => '081234567004', 'alamat' => 'Jl. Ahmad Yani No. 8'],
        ];

        foreach ($pelanggan as $item) {
            Pelanggan::updateOrCreate(['no_hp' => $item['no_hp']], $item);
        }
    }
}
