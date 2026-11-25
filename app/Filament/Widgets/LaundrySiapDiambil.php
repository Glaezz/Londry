<?php

namespace App\Filament\Widgets;

use App\Enums\StatusLaundry;
use App\Models\Transaksi;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LaundrySiapDiambil extends BaseWidget
{
    protected static ?string $heading = 'Laundry Siap Diambil';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaksi::query()
                    ->where('status_laundry', StatusLaundry::Selesai)
                    ->latest('tanggal_selesai')
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->label('Kode'),
                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Pelanggan'),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Pembayaran')
                    ->badge(),
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\Action::make('lunasiDanAmbil')
                    ->label('Lunasi & Ambil')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Transaksi $record): bool => $record->status_pembayaran->value === 'belum_bayar')
                    ->action(fn (Transaksi $record) => $record->lunasiDanAmbil()),
            ]);
    }
}
