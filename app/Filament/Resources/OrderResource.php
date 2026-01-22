<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $tenantOwnershipRelationshipName = 'store';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Bagian Kiri: Info Utama
                Section::make('Info Order')
                    ->columns(2)
                    ->schema([
                        TextInput::make('invoice_number')
                            ->disabled() // Gaboleh diedit
                            ->dehydrated(false), // Gak perlu disave lagi

                        TextInput::make('customer_name')
                            ->label('Nama Pelanggan'),

                        Select::make('payment_method')
                            ->options([
                                'cash' => 'Tunai',
                                'qris' => 'QRIS',
                            ])
                            ->required(),

                        Select::make('status')
                            ->options([
                                'pending' => 'Pending (Dimasak)',
                                'ready'   => 'Selesai (Ready)',
                                'cancel'  => 'Dibatalkan',
                            ])
                            ->required(),
                    ]),

                // Bagian Bawah: Daftar Menu yang Dibeli
                Section::make('Daftar Menu')
                    ->schema([
                        Repeater::make('items') // Relasi ke items()
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->label('Menu')
                                    ->disabled(), // Readonly aja

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->label('Qty')
                                    ->disabled(),

                                TextInput::make('price')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->label('Harga Satuan')
                                    ->disabled(),
                            ])
                            ->addable(false) // Gaboleh tambah item manual
                            ->deletable(false) // Gaboleh hapus item
                            ->columnSpanFull()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Nomor Invoice (Bisa dicari)
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // 2. Nama Pelanggan
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),

                // 3. Total Harga (Format Rupiah)
                TextColumn::make('total_price')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->sortable(),

                // 4. Metode Bayar
                TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'qris' => 'info',
                        default => 'gray',
                    }),

                // 5. Status Pesanan (Warna-warni)
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning', // Kuning (Belum dimasak)
                        'ready'   => 'success', // Hijau (Siap Saji)
                        'cancel'  => 'danger',  // Merah (Batal)
                        default   => 'gray',
                    }),

                // 6. Tanggal Transaksi
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Order terbaru paling atas
            ->filters([
                // Nanti kita bisa tambah filter tanggal di sini
            ])
            ->actions([
                // Tombol Edit/View
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
