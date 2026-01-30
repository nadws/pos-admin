<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    // Agar widget otomatis refresh setiap 10 detik tanpa reload halaman
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $storeId = filament()->getTenant()->id;
        return [
            Stat::make('Total Order', Order::where('store_id', $storeId)->count())
                ->description('Semua Orderan')
                ->descriptionIcon('heroicon-m-rectangle-stack', IconPosition::Before)
                ->chart([1, 3, 5, 2, 8, 10, 15]) // Mini chart
                ->color('primary'),

            Stat::make('Pemasukan', 'Rp ' . Number::abbreviate(
                Order::where('status', 'ready')->where('store_id', $storeId)->sum('total_price'),
                precision: 1
            ))
                ->description('Total Penjualan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([1, 3, 5, 7, 8, 10, 15])
                ->color('success'),

            Stat::make('Pengeluaran', 'Rp ' . Number::abbreviate(
                Purchase::where('store_id', $storeId)
                    ->withSum('items', 'subtotal')
                    ->get()
                    ->sum('items_sum_subtotal'), // Baru di sini kita sum hasil aggregatnya
                precision: 1
            ))
                ->description('Total Pembelian')
                ->descriptionIcon('heroicon-m-credit-card', IconPosition::Before)
                ->chart([1, 3, 5, 2, 8, 10, 15])
                ->color('info'),

            Stat::make('Jumlah Produk', Product::where('is_available', '1')->where('store_id', $storeId)->count())
                ->description('Jumlah Produk')
                ->descriptionIcon('heroicon-m-clipboard-document-list', IconPosition::Before)
                ->color('danger'),
        ];
    }
}
