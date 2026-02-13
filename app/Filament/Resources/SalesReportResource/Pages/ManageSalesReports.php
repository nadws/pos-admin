<?php

namespace App\Filament\Resources\SalesReportResource\Pages;

use App\Filament\Resources\SalesReportResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSalesReports extends ManageRecords
{
    protected static string $resource = SalesReportResource::class;

    protected function getHeaderActions(): array
    {
        return []; // Kosongkan agar tidak ada tombol "New"
    }
}
