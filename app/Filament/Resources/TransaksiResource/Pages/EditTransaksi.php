<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Enums\StatusLaundry;
use App\Filament\Resources\TransaksiResource;
use App\Models\Transaksi;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaksi extends EditRecord
{
    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetakStruk')
                ->label('Cetak Struk')
                ->icon('heroicon-o-printer')
                ->url(fn (Transaksi $record): string => route('transaksi.struk', $record), shouldOpenInNewTab: true),
            Actions\DeleteAction::make()
                ->visible(fn (Transaksi $record): bool => $record->status_laundry !== StatusLaundry::Diambil),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['kode_transaksi'], $data['user_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncTotal();
    }
}
