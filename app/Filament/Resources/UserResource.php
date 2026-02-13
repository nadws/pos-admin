<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList; // Import untuk pilih banyak toko
use Filament\Forms\Get;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static ?string $navigationLabel = 'Kelola User';


    protected static ?string $tenantOwnershipRelationshipName = 'stores';
    protected static ?string $tenantRelationshipName = 'members';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. Nama User
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                // 2. Email
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                // 3. Password
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),

                // 4. Role (Owner / Staff)
                Select::make('role')
                    ->options([
                        'owner' => 'Owner (Pemilik)',
                        'staff' => 'Staff (Kasir/Dapur)',
                    ])
                    ->default('staff')
                    ->required()
                    ->live(), // Agar form bereaksi real-time

                // 5. Pilih Toko (Bisa Banyak / Many-to-Many)
                CheckboxList::make('stores')
                    ->label('Tugaskan di Toko:')
                    ->relationship(
                        name: 'stores', // Relasi di Model User
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query) {

                            return $query->where('stores.user_id', \Illuminate\Support\Facades\Auth::id());
                        }
                    )
                    ->columns(2) // Tampil 2 kolom biar rapi
                    ->gridDirection('row')
                    ->visible(fn(Get $get) => $get('role') === 'staff') // Sembunyi kalau role Owner
                    ->required(fn(Get $get) => $get('role') === 'staff'), // Wajib kalau role Staff
                TextInput::make('pin')
                    ->label('PIN Keamanan (6 Digit)')
                    ->password() // Agar tidak terlihat saat diketik
                    ->revealable() // User bisa klik mata untuk melihat
                    ->maxLength(6)
                    ->minLength(6)
                    ->numeric()
                    ->helperText('Gunakan 6 digit angka untuk login kasir di aplikasi mobile.')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'owner' => 'success',
                        'staff' => 'warning',
                        default => 'gray',
                    }),

                // Menampilkan berapa toko yang dipegang staff tersebut
                Tables\Columns\TextColumn::make('stores_count')
                    ->counts('stores')
                    ->label('Jml Toko'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
