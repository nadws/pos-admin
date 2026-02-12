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
use Filament\Facades\Filament;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $tenantOwnershipRelationshipName = 'store';

    /**
     * SATPAM 1: Mengatur siapa yang bisa melihat menu di Sidebar
     */
    public static function canViewAny(): bool
    {
        $tenant = Filament::getTenant();
        $user = auth()->user();

        // 1. Super Admin selalu bebas masuk
        if ($user->is_admin) {
            return true;
        }

        // Jika tenant tidak ditemukan (misal saat proses login), sembunyikan menu
        if (!$tenant) {
            return false;
        }

        // 2. Jika Toko sudah lunas (is_active = 1), cek masa aktifnya
        if ($tenant->is_active) {
            return $tenant->subscription_until === null || now()->lte($tenant->subscription_until);
        }

        // 3. Jika belum lunas, cek apakah masih dalam masa trial 30 hari
        $daysSinceJoined = $tenant->created_at->diffInDays(now());

        return $daysSinceJoined <= 30;
    }

    /**
     * SATPAM 2: Mengunci data agar tidak muncul jika diakses lewat URL langsung
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        $tenant = Filament::getTenant();

        // Admin bisa melihat semua
        if ($user->is_admin) {
            return $query;
        }

        if (!$tenant) {
            return $query->whereRaw('1 = 0');
        }

        // Logika Gembok: Harus Lunas ATAU Masih Trial
        $daysSinceJoined = $tenant->created_at->diffInDays(now());
        $isSubscriptionActive = $tenant->is_active && ($tenant->subscription_until === null || now()->lte($tenant->subscription_until));
        $isTrialActive = !$tenant->is_active && $daysSinceJoined <= 30;

        if (!$isSubscriptionActive && !$isTrialActive) {
            // Jika expired dan tidak lunas, kosongkan hasil query
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
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

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Harga'),

                TextInput::make('stock')
                    ->numeric()
                    ->default(100)
                    ->label('Stok Awal'),

                FileUpload::make('image')
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public')
                    ->image(),

                Toggle::make('is_available')
                    ->label('Tersedia?')
                    ->default(true),

                Textarea::make('description')
                    ->columnSpanFull(),
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
                    ->searchable(),

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
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
