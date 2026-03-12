<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\UnitConversion;
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

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = Filament::getTenant();

        if ($user && $user->is_admin) {
            return true;
        }

        if (!$tenant) return false;

        if ($tenant->is_active) {
            return $tenant->subscription_until === null || now()->lte($tenant->subscription_until);
        }

        $daysSinceJoined = $tenant->created_at->diffInDays(now());
        return $daysSinceJoined <= 30;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = Filament::getTenant();

        if ($user && $user->is_admin) return $query;
        if (!$tenant) return $query->whereRaw('1 = 0');

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
                            // 1. Pilih Produk
                            Forms\Components\Select::make('product_id')
                                ->label('Produk')
                                ->relationship(
                                    'product',
                                    'name',
                                    modifyQueryUsing: fn($query) => $query->where('store_id', Filament::getTenant()->id)
                                )
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live() // Penting agar dropdown satuan di bawahnya berubah
                                ->afterStateUpdated(fn($set) => $set('unit_id', null)),

                            // 2. Pilih Satuan (Kg/Gr sesuai konversi produk)
                            Forms\Components\Select::make('unit_id')
                                ->label('Satuan Beli')
                                ->placeholder('Pilih Satuan')
                                ->options(function (Forms\Get $get) {
                                    $productId = $get('product_id');
                                    if (!$productId) return [];

                                    $product = Product::with(['unit', 'unitConversions.fromUnit'])->find($productId);
                                    if (!$product) return [];

                                    // Satuan Dasar
                                    $options = [];
                                    if ($product->unit) {
                                        $options[$product->unit_id] = $product->unit->name . ' (Dasar)';
                                    }

                                    // Satuan Konversi (Kg, Karung, dll)
                                    foreach ($product->unitConversions as $conversion) {
                                        if ($conversion->fromUnit) {
                                            $options[$conversion->from_unit_id] = $conversion->fromUnit->name;
                                        }
                                    }

                                    return $options;
                                })
                                ->required()
                                ->live(),

                            // 3. Jumlah Beli
                            Forms\Components\TextInput::make('qty')
                                ->label('Jumlah')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($set, $get) {
                                    $qty = (float) ($get('qty') ?? 0);
                                    $price = (float) ($get('price') ?? 0);
                                    $set('subtotal', $qty * $price);
                                }),

                            // 4. Harga Satuan
                            Forms\Components\TextInput::make('price')
                                ->label('Harga Satuan')
                                ->numeric()
                                ->required()
                                ->prefix('Rp')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($set, $get) {
                                    $qty = (float) ($get('qty') ?? 0);
                                    $price = (float) ($get('price') ?? 0);
                                    $set('subtotal', $qty * $price);
                                }),

                            // 5. Subtotal (Otomatis)
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->disabled()
                                ->dehydrated()
                                ->prefix('Rp'),
                        ])
                        ->columns(5)
                        ->defaultItems(1)
                        ->addActionLabel('Tambah Item Belanja')
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
                Tables\Actions\DeleteAction::make(),
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
