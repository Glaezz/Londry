<?php

namespace App\Filament\Resources;

use App\Enums\StatusLaundry;
use App\Enums\StatusPembayaran;
use App\Filament\Resources\TransaksiResource\Pages;
use App\Models\Layanan;
use App\Models\Transaksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Transaksi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('kode_transaksi')
                            ->label('Kode Transaksi')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Dibuat otomatis'),
                        Forms\Components\Select::make('pelanggan_id')
                            ->label('Pelanggan')
                            ->relationship('pelanggan', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('no_hp')
                                    ->label('No. HP')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20),
                                Forms\Components\Textarea::make('alamat')
                                    ->label('Alamat'),
                            ]),
                        Forms\Components\DateTimePicker::make('tanggal_masuk')
                            ->label('Tanggal Masuk')
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Item Layanan')
                    ->schema([
                        Forms\Components\Repeater::make('detail')
                            ->relationship('detail')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Select::make('layanan_id')
                                    ->label('Layanan')
                                    ->options(fn (): Collection => Layanan::pluck('nama_layanan', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        $set('harga_satuan', (float) (Layanan::find($state)?->harga_per_unit ?? 0));
                                    }),
                                Forms\Components\TextInput::make('jumlah_unit')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(0.1)
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('harga_satuan')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Placeholder::make('subtotal_preview')
                                    ->label('Subtotal')
                                    ->content(fn (Get $get): string => 'Rp '.number_format((float) $get('jumlah_unit') * (float) $get('harga_satuan'), 0, ',', '.')),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->required()
                            ->disabled(fn (?Transaksi $record): bool => $record?->status_laundry === StatusLaundry::Diambil)
                            ->addActionLabel('Tambah Item')
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => TransaksiResource::hitungItem($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => TransaksiResource::hitungItem($data)),
                        Forms\Components\Placeholder::make('total_preview')
                            ->label('Total Bayar')
                            ->content(fn (Get $get): string => 'Rp '.number_format(
                                collect($get('detail') ?? [])->sum(fn (array $item): float => (float) ($item['jumlah_unit'] ?? 0) * (float) ($item['harga_satuan'] ?? 0)),
                                0,
                                ',',
                                '.',
                            )),
                    ]),
            ]);
    }

    /**
     * Recalculate one item from the database, ignoring browser-supplied prices.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function hitungItem(array $item): array
    {
        $jumlahUnit = round((float) ($item['jumlah_unit'] ?? 0), 2);
        $hargaSatuan = round((float) (Layanan::find($item['layanan_id'] ?? null)?->harga_per_unit ?? 0), 2);

        $item['harga_satuan'] = $hargaSatuan;
        $item['subtotal'] = round($jumlahUnit * $hargaSatuan, 2);

        return $item;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_bayar')
                    ->label('Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_laundry')
                    ->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Pembayaran')
                    ->badge(),
            ])
            ->defaultSort('tanggal_masuk', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status_laundry')
                    ->label('Status Laundry')
                    ->options(StatusLaundry::class),
                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options(StatusPembayaran::class),
                Tables\Filters\Filter::make('tanggal_masuk')
                    ->label('Rentang Tanggal Masuk')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari'),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, string $dari): Builder => $q->whereDate('tanggal_masuk', '>=', $dari))
                        ->when($data['sampai'] ?? null, fn (Builder $q, string $sampai): Builder => $q->whereDate('tanggal_masuk', '<=', $sampai))),
            ])
            ->actions([
                Tables\Actions\Action::make('lanjutkanStatus')
                    ->label('Lanjutkan Status')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->requiresConfirmation()
                    ->visible(fn (Transaksi $record): bool => $record->status_laundry->next() !== null)
                    ->action(fn (Transaksi $record) => $record->lanjutkanStatus()),
                Tables\Actions\Action::make('lunasiDanAmbil')
                    ->label('Lunasi dan Tandai Diambil')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Transaksi $record): bool => $record->status_laundry === StatusLaundry::Selesai
                        && $record->status_pembayaran === StatusPembayaran::BelumBayar)
                    ->action(fn (Transaksi $record) => $record->lunasiDanAmbil()),
                Tables\Actions\Action::make('tandaiLunas')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Transaksi $record): bool => $record->status_pembayaran === StatusPembayaran::BelumBayar)
                    ->action(fn (Transaksi $record) => $record->tandaiLunas()),
                Tables\Actions\Action::make('cetakStruk')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Transaksi $record): string => route('transaksi.struk', $record), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}
