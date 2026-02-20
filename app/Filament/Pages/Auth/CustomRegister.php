<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Auth\Register as BaseRegister;

class CustomRegister extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->makeForm()
                ->schema([
                    $this->getNameFormComponent(),
                    $this->getEmailFormComponent(),

                    // Input Nomor HP
                    TextInput::make('phone')
                        ->label('Nomor WhatsApp')
                        ->tel()
                        ->placeholder('08xxxxxxxxxx')
                        ->required()
                        // Validasi: Harus angka, minimal 10 digit, maksimal 15 digit
                        ->numeric()
                        ->minLength(10)
                        ->maxLength(15)
                        // Validasi Format: Harus mulai dengan 08 atau 62
                        ->regex('/^(08|62)[\d]{8,13}$/')
                        ->validationMessages([
                            'regex' => 'Format nomor WhatsApp tidak valid. Gunakan awalan 08 atau 62.',
                            'numeric' => 'Nomor WhatsApp harus berupa angka.',
                            'minLength' => 'Nomor terlalu pendek.',
                        ])
                        // Memastikan nomor HP belum pernah didaftarkan sebelumnya
                        ->unique('users', 'phone'),

                    // Input Alamat
                    Textarea::make('address')
                        ->label('Alamat Lengkap')
                        ->placeholder('Contoh: Jl. Sudirman No. 123...')
                        ->required()
                        ->rows(3),

                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                ])
                ->statePath('data'),
        ];
    }
}
