<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Store;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth;

class RegisterStore extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Buat Usaha Baru';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Usaha')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) =>
                    $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->label('URL Slug')
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                TextInput::make('address')
                    ->label('Alamat'),
            ]);
    }

    protected function handleRegistration(array $data): Store
    {
        // 1. Simpan ID Pemilik Asli (Owner)
        $data['user_id'] = Auth::id();

        // 2. Buat Toko Baru
        $store = Store::create($data);

        // 3. 👇 INI YANG KURANG: Hubungkan User yang login ke Toko Baru (Pivot)
        auth()->user()->stores()->attach($store);

        return $store;
    }
}
