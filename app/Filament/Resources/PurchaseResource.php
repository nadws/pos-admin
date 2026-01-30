<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Stock Masuk';
    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $tenantOwnershipRelationshipName = 'store';


    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('invoice_number')
                ->label('No Invoice')
                ->default(fn() => 'INV-' . now()->format('Ymd-His'))
                ->disabled()
                ->dehydrated(),


            Forms\Components\TextInput::make('supplier_name')
                ->label('Supplier'),

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
                        ->required(),

                    Forms\Components\TextInput::make('qty')
                        ->numeric()
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($set, $get) {
                            $set(
                                'subtotal',
                                ((int) $get('qty')) * ((int) $get('price'))
                            );
                        }),


                    Forms\Components\TextInput::make('price')
                        ->numeric()
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($set, $get) {
                            $set(
                                'subtotal',
                                ((int) $get('qty')) * ((int) $get('price'))
                            );
                        }),


                    Forms\Components\TextInput::make('subtotal')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(), // tetap dikirim ke DB
                ])
                ->columns(4)
                ->defaultItems(1)
                ->columnSpanFull(),

        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Nomor Invoice
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),

                // Supplier
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Supplier')
                    ->searchable(),

                // Total Item (hasil sum qty dari items)
                Tables\Columns\TextColumn::make('items_sum_qty')
                    ->label('Total Qty')
                    ->sum('items', 'qty')
                    ->sortable(),

                // Total Harga (hasil sum subtotal)
                Tables\Columns\TextColumn::make('items_sum_subtotal')
                    ->label('Total Belanja')
                    ->sum('items', 'subtotal')
                    ->money('IDR')
                    ->sortable(),

                // Tanggal
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // Filter tanggal (opsional)
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(
                        fn($query) =>
                        $query->whereDate('created_at', now())
                    ),
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


    public static function getRelations(): array
    {
        return [
            //
        ];
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
