<?php

namespace App\Filament\Resources\StoreResource\Pages;

use App\Filament\Resources\StoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

class EditStore extends EditRecord
{
    protected static string $resource = StoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * UBAH MENJADI PUBLIC
     * Fungsi ini akan merender file blade qr-header tepat di atas form edit
     */
    public function getHeader(): ?View
    {
        return view('filament.stores.qr-header', [
            'record' => $this->getRecord(),
        ]);
    }
}
