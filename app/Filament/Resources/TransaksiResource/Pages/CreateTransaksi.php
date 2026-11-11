<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateTransaksi extends CreateRecord
{
    protected static string $resource = TransaksiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kode_transaksi'] = $this->generateKodeTransaksi();
        $data['user_id'] = Auth::id();
        $data['status_laundry'] = $data['status_laundry'] ?? 'baru';
        $data['status_pembayaran'] = $data['status_pembayaran'] ?? 'belum_bayar';
        $data['total_bayar'] = 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncTotal();
    }

    /**
     * Build a daily-reset code such as TRX-20261111-001.
     *
     * ponytail: last-code lookup is race-safe only inside a DB transaction
     * (panel ->databaseTransactions()); a unique-index retry is the upgrade path.
     */
    protected function generateKodeTransaksi(): string
    {
        $prefix = 'TRX-'.now()->format('Ymd').'-';

        $lastKode = DB::table('transaksi')
            ->where('kode_transaksi', 'like', $prefix.'%')
            ->orderByDesc('kode_transaksi')
            ->lockForUpdate()
            ->value('kode_transaksi');

        $nextNumber = $lastKode ? ((int) substr($lastKode, -3)) + 1 : 1;

        return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
