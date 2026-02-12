<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Facades\Filament;

class Billing extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static string $view = 'filament.pages.billing';
    protected static ?string $title = 'Langganan & Pembayaran';

    public static function shouldRegisterNavigation(): bool
    {
        $tenant = \Filament\Facades\Filament::getTenant();

        // Hitung apakah sudah lewat 30 hari sejak daftar
        $isTrialExpired = $tenant->created_at->diffInDays(now()) > 30;

        // Menu Billing muncul jika belum bayar ATAU masa percobaan habis
        return ! $tenant->is_active || $isTrialExpired;
    }
}
