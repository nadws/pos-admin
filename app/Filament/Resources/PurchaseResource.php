<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Faktur Pembelian';
    protected static ?string $navigationGroup = 'Manajemen Produk';
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
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pembelian')
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')
                        ->label('No Invoice')
                        ->default(fn() => 'INV-' . now()->format('Ymd-His'))
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('supplier_name')
                        ->label('Supplier'),
                ])->columns(2),

            Forms\Components\Section::make('Item Produk')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Produk')
                                ->relationship(
                                    'product',
                                    'name',
                                    modifyQueryUsing: fn($query) =>
                                    $query->where('store_id', Filament::getTenant()->id)
                                )
                                ->required()
                                ->searchable()
                                ->preload(),

                            Forms\Components\TextInput::make('qty')
                                ->label('Jumlah')
                                ->numeric()
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($set, $get) {
                                    $set('subtotal', ((int) $get('qty')) * ((int) $get('price')));
                                }),

                            Forms\Components\TextInput::make('price')
                                ->label('Harga Satuan')
                                ->numeric()
                                ->required()
                                ->prefix('Rp')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($set, $get) {
                                    $set('subtotal', ((int) $get('qty')) * ((int) $get('price')));
                                }),

                            Forms\Components\TextInput::make('subtotal')
                                ->numeric()
                                ->disabled()
                                ->dehydrated()
                                ->prefix('Rp'),
                        ])
                        ->columns(4)
                        ->defaultItems(1)
                        ->columnSpanFull(),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Supplier')
                    ->searchable(),

                Tables\Columns\TextColumn::make('items_sum_qty')
                    ->label('Total Qty')
                    ->sum('items', 'qty')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('items_sum_subtotal')
                    ->label('Total Belanja')
                    ->sum('items', 'subtotal')
                    ->money('IDR')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn($query) => $query->whereDate('created_at', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
