<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\UnitConversion;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        // TAB 1: UMUM & SATUAN (Alur Gambar 2)
                        Tabs\Tab::make('Umum & Satuan')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Kategori')
                                            ->relationship('category', 'name', fn($query) => $query->where('store_id', Filament::getTenant()->id))
                                            ->required()
                                            ->live(),

                                        TextInput::make('name')->required()->label('Nama Barang / Menu'),

                                        Select::make('unit_id')
                                            ->label('Satuan Dasar (Terkecil)')
                                            ->helperText('Contoh: Gram, Ml, Pcs')
                                            ->relationship('unit', 'name', fn($query) => $query->where('store_id', Filament::getTenant()->id))
                                            ->createOptionUsing(fn($data) => Unit::create(['name' => $data['name'], 'store_id' => Filament::getTenant()->id])->id)
                                            ->required()
                                            ->live(),

                                        TextInput::make('price')->required()->numeric()->prefix('Rp')->label('Harga Jual'),

                                        Repeater::make('unitConversions')
                                            ->relationship('unitConversions')
                                            ->label('Konversi Satuan (Opsional)')
                                            ->schema([
                                                Select::make('from_unit_id')
                                                    ->label('Jika Satuan Adalah')
                                                    ->relationship('fromUnit', 'name', fn($query) => $query->where('store_id', Filament::getTenant()->id))
                                                    ->required(),

                                                TextInput::make('multiplier')
                                                    ->label('Maka Isinya Adalah')
                                                    ->numeric()
                                                    ->suffix(fn(Get $get) => Unit::find($get('../../unit_id'))?->name ?? 'Satuan Dasar')
                                                    ->required(),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->visible(fn(Get $get) => str_contains(strtolower(Category::find($get('category_id'))?->name ?? ''), 'bahan baku')),
                                    ]),
                            ]),

                        // TAB 2: RESEP (Disesuaikan dengan relasi 'recipes' di Model Mas)
                        Tabs\Tab::make('Resep / Komposisi')
                            ->icon('heroicon-m-beaker')
                            ->visible(fn(Get $get) => !str_contains(strtolower(Category::find($get('category_id'))?->name ?? ''), 'bahan baku'))
                            ->schema([
                                Toggle::make('is_recipe')->label('Gunakan Resep?')->live(),

                                Repeater::make('recipes') // Nama ini HARUS SAMA dengan fungsi di Model Product
                                    ->relationship('recipes')
                                    ->label('Daftar Bahan Baku')
                                    ->visible(fn(Get $get) => $get('is_recipe'))
                                    ->schema([
                                        Select::make('ingredient_id')
                                            ->label('Bahan Baku')
                                            ->options(fn() => Product::where('store_id', Filament::getTenant()->id)
                                                ->whereHas('category', fn($q) => $q->where('name', 'LIKE', '%Bahan Baku%'))
                                                ->pluck('name', 'id'))
                                            ->required()
                                            ->live(),

                                        Select::make('unit_id')
                                            ->label('Satuan Pakai')
                                            ->options(function (Get $get) {
                                                $prodId = $get('ingredient_id');
                                                if (!$prodId) return [];

                                                $prod = Product::find($prodId);
                                                if (!$prod) return [];

                                                $baseUnit = [$prod->unit_id => $prod->unit?->name];
                                                $conversions = UnitConversion::where('product_id', $prodId)
                                                    ->with('fromUnit')
                                                    ->get()
                                                    ->pluck('fromUnit.name', 'fromUnit.id')
                                                    ->toArray();

                                                return $baseUnit + $conversions;
                                            })
                                            ->required()
                                            ->live(),

                                        TextInput::make('quantity')
                                            ->label('Jumlah')
                                            ->numeric()
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->addActionLabel('Tambah Bahan'),
                            ]),

                        // TAB 3: STOK
                        Tabs\Tab::make('Stok')
                            ->icon('heroicon-m-cube')
                            ->schema([
                                Toggle::make('track_stock')->label('Lacak Stok?')->default(true)->live(),
                                TextInput::make('stock')
                                    ->label('Saldo Terkini')
                                    ->numeric()
                                    ->suffix(fn(Get $get) => Unit::find($get('unit_id'))?->name ?? '')
                                    ->visible(fn(Get $get) => $get('track_stock') && !$get('is_recipe')),

                                Forms\Components\Placeholder::make('recipe_info')
                                    ->label('Info Stok')
                                    ->content('Stok produk ini mengikuti ketersediaan bahan baku di resep.')
                                    ->visible(fn(Get $get) => $get('track_stock') && $get('is_recipe')),
                            ]),

                        Tabs\Tab::make('Gambar')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                FileUpload::make('image')->disk('public')->directory('products')->image()->imageEditor()->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->activeTab(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Foto'),
                Tables\Columns\TextColumn::make('name')->label('Nama Menu')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori'),
                Tables\Columns\TextColumn::make('price')->label('Harga')->money('IDR'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->formatStateUsing(fn($record) => $record->is_recipe ? 'Auto' : $record->stock . ' ' . ($record->unit?->name ?? '')),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
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
