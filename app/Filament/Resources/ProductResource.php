<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Facades\Filament;
use Filament\Forms\Components\Tabs;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $tenantOwnershipRelationshipName = 'store';

    public static function canViewAny(): bool
    {
        $tenant = Filament::getTenant();
        $user = auth()->user();

        if ($user->is_admin) {
            return true;
        }

        if (!$tenant) {
            return false;
        }

        if ($tenant->is_active) {
            return $tenant->subscription_until === null || now()->lte($tenant->subscription_until);
        }

        $daysSinceJoined = $tenant->created_at->diffInDays(now());
        return $daysSinceJoined <= 30;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        $tenant = Filament::getTenant();

        if ($user->is_admin) {
            return $query;
        }

        if (!$tenant) {
            return $query->whereRaw('1 = 0');
        }

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
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        // TAB 1: UMUM
                        Forms\Components\Tabs\Tab::make('Umum')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Kategori')
                                            ->relationship(
                                                name: 'category',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn(Builder $query) => $query->where('store_id', Filament::getTenant()->id)
                                            )
                                            ->required(),

                                        TextInput::make('name')
                                            ->required()
                                            ->label('Nama Menu'),

                                        Textarea::make('description')
                                            ->label('Keterangan Tambahan')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // TAB 2: PENJUALAN
                        Forms\Components\Tabs\Tab::make('Penjualan')
                            ->icon('heroicon-m-banknotes')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('price')
                                            ->required()
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('Harga Jual'),

                                        Toggle::make('is_available')
                                            ->label('Status Aktif / Tersedia')
                                            ->default(true),
                                    ]),
                            ]),

                        // TAB 3: STOK & SATUAN
                        // TAB 3: STOK & SATUAN
                        Forms\Components\Tabs\Tab::make('Stok')
                            ->icon('heroicon-m-cube')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Toggle::make('track_stock')
                                            ->label('Lacak Stok untuk Produk Ini?')
                                            ->default(true)
                                            ->live()
                                            ->columnSpanFull(),

                                        // Field Stok Utama
                                        TextInput::make('stock')
                                            ->label('Jumlah Stok Saat Ini')
                                            ->numeric()
                                            ->default(0)
                                            // LOGIKA KAMU: Sembunyikan jika track_stock OFF atau jika ini adalah PRODUK RESEP
                                            ->visible(fn(Forms\Get $get) => $get('track_stock') && !$get('is_recipe'))
                                            ->required(fn(Forms\Get $get) => $get('track_stock') && !$get('is_recipe')),

                                        // Keterangan pengganti jika produk pakai resep
                                        Forms\Components\Placeholder::make('recipe_notice')
                                            ->label('Info Stok')
                                            ->content('Stok produk ini dihitung otomatis berdasarkan ketersediaan bahan baku di Tab Resep.')
                                            ->visible(fn(Forms\Get $get) => $get('track_stock') && $get('is_recipe'))
                                            ->columnSpan(1),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Resep')
                            ->icon('heroicon-m-beaker')
                            ->visible(fn(Forms\Get $get) => $get('track_stock'))
                            ->schema([
                                Toggle::make('is_recipe')
                                    ->label('Produk ini Menggunakan Resep / Komposisi Bahan?')
                                    ->default(false)
                                    ->live()
                                    ->helperText('Jika aktif, stok akan memotong bahan baku secara otomatis.'),

                                Forms\Components\Repeater::make('recipes')
                                    ->relationship('recipes')
                                    ->visible(fn(Forms\Get $get) => $get('is_recipe'))
                                    ->schema([
                                        Select::make('ingredient_id')
                                            ->label('Pilih Bahan Baku')
                                            ->options(function () {
                                                return \App\Models\Product::where('store_id', \Filament\Facades\Filament::getTenant()->id)
                                                    ->whereHas('category', function ($query) {
                                                        $query->where('name', 'Bahan Baku');
                                                    })
                                                    ->pluck('name', 'id');
                                            })
                                            ->searchable()
                                            ->required()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                                        TextInput::make('quantity')
                                            ->label('Jumlah yang Digunakan')
                                            ->numeric()
                                            ->required()
                                            ->suffix(function (Forms\Get $get) {
                                                // Menampilkan singkatan satuan secara live
                                                $item = \App\Models\Product::find($get('ingredient_id'));
                                                return $item?->unit?->short_name ?? '';
                                            }),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(1)
                                    ->addActionLabel('Tambah Bahan Baku')
                            ]),

                        // TAB 5: MEDIA
                        Forms\Components\Tabs\Tab::make('Gambar')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                FileUpload::make('image')
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->image()
                                    ->imageEditor() // Tambahan biar bisa crop gambar
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->activeTab(1), // Default buka tab pertama
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->label('Foto'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Menu')
                    ->searchable()
                    ->description(fn(Product $record) => $record->is_recipe ? 'Menggunakan Resep' : 'Barang Jadi'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn(Product $record) => $record->stock <= 5 ? 'danger' : 'success')
                    ->formatStateUsing(fn(Product $record) => $record->is_recipe ? 'Auto (Resep)' : $record->stock),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Aktif')
                    ->boolean(),
            ])
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
