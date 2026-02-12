<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $tenantOwnershipRelationshipName = 'store';

    /**
     * SATPAM 1: Mengunci menu di Sidebar
     */
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = Filament::getTenant();

        // Super Admin bebas lewat
        if ($user && $user->is_admin) {
            return true;
        }

        if (!$tenant) return false;

        // Cek Status Lunas & Masa Aktif
        if ($tenant->is_active) {
            return $tenant->subscription_until === null || now()->lte($tenant->subscription_until);
        }

        // Cek Masa Trial 30 Hari
        $daysSinceJoined = $tenant->created_at->diffInDays(now());
        return $daysSinceJoined <= 30;
    }

    /**
     * SATPAM 2: Mengunci data agar tidak muncul jika diakses lewat URL
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = Filament::getTenant();

        if ($user && $user->is_admin) return $query;
        if (!$tenant) return $query->whereRaw('1 = 0');

        // Logika Gembok Otomatis
        $daysSinceJoined = $tenant->created_at->diffInDays(now());
        $isSubscriptionActive = $tenant->is_active && ($tenant->subscription_until === null || now()->lte($tenant->subscription_until));
        $isTrialActive = !$tenant->is_active && $daysSinceJoined <= 30;

        if (!$isSubscriptionActive && !$isTrialActive) {
            return $query->whereRaw('1 = 0'); // Paksa data kosong
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Info Order')
                    ->columns(2)
                    ->schema([
                        TextInput::make('invoice_number')
                            ->disabled()
                            ->dehydrated(false),

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

                Section::make('Daftar Menu')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->label('Menu')
                                    ->disabled(),

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
                            ->addable(false)
                            ->deletable(false)
                            ->columns(3)
                            ->columnSpanFull()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),

                TextColumn::make('total_price')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'qris' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'ready'   => 'success',
                        'cancel'  => 'danger',
                        default   => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
