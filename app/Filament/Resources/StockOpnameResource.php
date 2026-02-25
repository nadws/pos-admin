<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOpnameResource\Pages;
use App\Models\StockOpname;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Stock Opname';
    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $tenantOwnershipRelationshipName = 'store';

    /**
     * SATPAM 1: Mengunci menu di Sidebar agar tidak bisa diklik jika trial habis/tidak langganan
     */
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = Filament::getTenant();

        if ($user && $user->is_admin) return true;
        if (!$tenant) return false;

        if ($tenant->is_active) {
            return $tenant->subscription_until === null || now()->lte($tenant->subscription_until);
        }

        $daysSinceJoined = $tenant->created_at->diffInDays(now());
        return $daysSinceJoined <= 30;
    }

    /**
     * SATPAM 2: Mengunci data agar tidak muncul jika diakses paksa lewat URL
     */
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
            Forms\Components\Section::make('Informasi Opname')
                ->schema([
                    Forms\Components\TextInput::make('reference_number')
                        ->label('No. Referensi')
                        ->default(fn() => 'SO-' . now()->format('Ymd-His'))
                        ->readonly()
                        ->dehydrated(),

                    Forms\Components\DatePicker::make('date')
                        ->label('Tanggal Opname')
                        ->default(now())
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->placeholder('Contoh: Penyesuaian stok gudang pusat')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft (Belum Update Stok)',
                            'published' => 'Published (Update Stok ke Sistem)',
                        ])
                        ->default('draft')
                        ->required()
                        ->native(false),
                ])->columns(2),

            Forms\Components\Section::make('Item Produk')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship() // Pastikan sudah ada relasi items() di model StockOpname
                        ->schema([
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
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        $set('system_stock', $product?->stock ?? 0);
                                    }
                                }),

                            Forms\Components\TextInput::make('system_stock')
                                ->label('Stok Sistem')
                                ->numeric()
                                ->readonly()
                                ->dehydrated()
                                ->hint('Stok saat ini'),

                            Forms\Components\TextInput::make('actual_stock')
                                ->label('Stok Fisik')
                                ->numeric()
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $get, $set) {
                                    $systemStock = (int) $get('system_stock');
                                    $actualStock = (int) $state;
                                    $set('difference', $actualStock - $systemStock);
                                }),

                            Forms\Components\TextInput::make('difference')
                                ->label('Selisih')
                                ->numeric()
                                ->readonly()
                                ->dehydrated()
                                ->hint(fn($state) => $state < 0 ? 'Kurang' : ($state > 0 ? 'Berlebih' : 'Sesuai'))
                                ->hintColor(fn($state) => $state < 0 ? 'danger' : ($state > 0 ? 'success' : 'gray')),
                        ])
                        ->columns(4)
                        ->defaultItems(1)
                        ->columnSpanFull()
                        ->reorderableWithButtons(),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Referensi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Total Item')
                    ->counts('items')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn($q) => $q->whereDate('date', '>=', $data['created_from']))
                            ->when($data['created_until'], fn($q) => $q->whereDate('date', '<=', $data['created_until']));
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Print Form')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(StockOpname $record) => route('stock-opname.print', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(), // Tambahkan edit agar bisa input hasil opname nanti
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
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
            // 'view' => Pages\ViewStockOpname::route('/{record}'),
        ];
    }
}
