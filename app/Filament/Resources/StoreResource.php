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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Administrasi';

    // Gunakan relasi members untuk kepemilikan tenant
    protected static ?string $tenantOwnershipRelationshipName = 'members';

    /** * Kita set false agar Filament tidak bingung mencari relasi 'stores' 
     * di dalam model Store itu sendiri.
     */
    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Cabang')
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
                    ])->columns(2),

                // --- SEKSI LANGGANAN (HANYA ADMIN YANG BISA LIHAT & EDIT) ---
                Forms\Components\Section::make('Status Berlangganan (Admin Only)')
                    ->description('Kelola aktivasi pembayaran 500rb di sini.')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktivasi (Lunas)')
                            ->onColor('success'),

                        Forms\Components\DatePicker::make('subscription_until')
                            ->label('Berlaku Hingga')
                            ->default(now()->addYear()),
                    ])
                    // KUNCI: Seksi ini hilang total jika bukan Admin login
                    ->visible(fn() => Auth::user()?->is_admin)
                    ->columns(2),

                Hidden::make('user_id')
                    ->default(fn() => Auth::id())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),

                // Kolom Lunas & Masa Aktif hanya muncul untuk mata Admin
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Lunas')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                    ->visible(fn() => Auth::user()?->is_admin),

                Tables\Columns\TextColumn::make('subscription_until')
                    ->label('Masa Aktif')
                    ->date()
                    ->color(fn($state) => $state && now()->gt($state) ? 'danger' : 'gray')
                    ->visible(fn() => Auth::user()?->is_admin),

                Tables\Columns\TextColumn::make('slug')->badge(),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika Admin, bisa lihat semua toko yang terdaftar
        if ($user && $user->is_admin) {
            return $query;
        }

        // Jika User biasa, hanya boleh lihat toko miliknya sendiri
        return $query->where('user_id', Auth::id());
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Setup Perangkat Mobile')
                    ->description('Scan QR Code ini melalui aplikasi mobile untuk menghubungkan perangkat kasir ke cabang ini.')
                    ->schema([
                        ViewEntry::make('qr_code')
                            ->label('QR Code Setup')
                            ->view('filament.stores.qr-code-setup')
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
