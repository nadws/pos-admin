<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Administrasi';
    // Matikan scope tenant agar daftar store bisa diakses global oleh owner
    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) =>
                    $set('slug', Str::slug($state)))
                    ->label('Nama Cabang'),

                TextInput::make('slug')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->unique(ignoreRecord: true),

                TextInput::make('address')
                    ->label('Alamat Lengkap'),

                FileUpload::make('logo')
                    ->directory('stores')
                    ->image(),

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

    // Fungsi Infolist untuk menampilkan QR Code
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Setup Perangkat Mobile')
                    ->description('Scan QR Code ini melalui aplikasi mobile untuk menghubungkan perangkat kasir ke cabang ini.')
                    ->schema([
                        ViewEntry::make('qr_code')
                            ->label('QR Code Setup')
                            ->view('filament.stores.qr-code-setup') // Mengarah ke file blade
                            ->columnSpan(1),

                        Section::make('Informasi Cabang')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama Cabang'),
                                TextEntry::make('slug')
                                    ->label('Store ID / Slug')
                                    ->badge(),
                            ])->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
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
