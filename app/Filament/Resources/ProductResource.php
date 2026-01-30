<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload; // Jangan lupa import ini
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;

use Filament\Facades\Filament;

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
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        // 👇 TAMBAHKAN LOGIKA FILTER INI 👇
                        modifyQueryUsing: function (Builder $query) {
                            // Ambil ID Toko yang sedang aktif di URL (Tenant)
                            $tenantId = Filament::getTenant()->id;

                            // Filter query: Hanya ambil kategori milik toko tersebut
                            return $query->where('store_id', $tenantId);
                        },
                    )
                    ->required(),

                // Nama Menu
                TextInput::make('name')
                    ->required()
                    ->label('Nama Menu'),

                // Harga (Pakai prefix Rp biar ganteng)
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Harga'),

                // Stok
                TextInput::make('stock')
                    ->numeric()
                    ->default(100)
                    ->label('Stok Awal'),

                // Upload Gambar
                FileUpload::make('image')
                    ->disk('public') // <--- Pastikan ada ini (atau sesuaikan default filesystem)
                    ->directory('products')
                    ->visibility('public') // <--- Pastikan ini public
                    ->image(),

                // Status Tersedia
                Toggle::make('is_available')
                    ->label('Tersedia?')
                    ->default(true),

                // Deskripsi
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Kolom Gambar (Taruh depan biar cakep)
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public') // <--- Paksa baca dari folder public
                    ->visibility('public')
                    ->label('Foto'),

                // 2. Nama Menu
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Menu')
                    ->searchable(),

                // 3. Kategori (Hapus numeric!)
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),

                // 4. Harga (Format Rupiah)
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR') // Otomatis jadi Rp ...
                    ->sortable(),

                // 5. Stok
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable(),

                // 6. Status Tersedia
                Tables\Columns\IconColumn::make('is_available')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
