<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockMutationsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMutations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('created_at')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'adjustment' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('quantity')->label('Jumlah')->numeric(),
                Tables\Columns\TextColumn::make('reference_stock')->label('Stok Awal'),
                Tables\Columns\TextColumn::make('description')->label('Keterangan'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
