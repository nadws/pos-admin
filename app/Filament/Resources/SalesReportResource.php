<?php

namespace App\Filament\Resources;

use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;

// Import Export
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $tenantOwnershipRelationshipName = 'store';

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = Filament::getTenant();

        if ($user && $user->is_admin) return true;
        if (!$tenant) return false;

        if ($tenant->is_active) {
            return $tenant->subscription_until === null || now()->lte($tenant->subscription_until);
        }

        return $tenant->created_at->diffInDays(now()) <= 30;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = Filament::getTenant();

        if ($user && $user->is_admin) return $query;
        if (!$tenant) return $query->whereRaw('1 = 0');

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->default('Umum'),

                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge(),

                TextColumn::make('total_price')
                    ->label('Total Omzet')
                    ->money('IDR')
                    ->summarize(
                        Sum::make()
                            ->label('Total Pendapatan')
                            ->money('IDR')
                    ),
            ])
            ->headerActions([
                // VERSI EKSPORT PALING AMAN (Tanpa method styling yang bikin error)
                Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        // Cara benar mengambil query yang terfilter di Filament V3
                        $query = $livewire->getFilteredTableQuery();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\SalesReportExport($query),
                            'Laporan_Penjualan_' . now()->format('Y-m-d') . '.xlsx'
                        );
                    }),

                Action::make('print')
                    ->label('Cetak Laporan')
                    ->color('gray')
                    ->icon('heroicon-o-printer')
                    ->extraAttributes([
                        'onclick' => 'window.print(); return false;',
                    ]),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['sampai_tanggal'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => SalesReportResource\Pages\ManageSalesReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit($record): bool
    {
        return false;
    }
}
