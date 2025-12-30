<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Filament\Resources\StoreResource\RelationManagers;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true) // Saat selesai ketik nama...
                    ->afterStateUpdated(fn(Set $set, ?string $state) =>
                    $set('slug', Str::slug($state))) // ...Otomatis isi slug
                    ->label('Nama Cabang'),

                // 2. Slug (URL Toko)
                TextInput::make('slug')
                    ->required()
                    ->disabled() // Biar gak diedit manual (atau boleh dihilangkan disabled-nya)
                    ->dehydrated() // Tetap dikirim ke database meski disabled
                    ->unique(ignoreRecord: true),

                // 3. Alamat
                TextInput::make('address')
                    ->label('Alamat Lengkap'),

                // 4. Logo (Opsional)
                FileUpload::make('logo')
                    ->directory('stores') // Simpan di folder storage/app/public/stores
                    ->image(),

                // 5. HIDDEN: Otomatis isi user_id dengan User yang sedang login
                Hidden::make('user_id')
                    ->default(fn() => auth()->id())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->badge(),
                Tables\Columns\TextColumn::make('address')->limit(30),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
