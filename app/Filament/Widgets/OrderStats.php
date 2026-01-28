<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;

class OrderStats extends ChartWidget
{
    protected static ?string $heading = 'Penjualan 7 Hari Terakhir';

    protected function getData(): array
    {
        $storeId = filament()->getTenant()->id;
        $data = Order::selectRaw('DATE(created_at) as tanggal, SUM(total_price) as total')
            ->whereDate('created_at', '>=', now()->subDays(6))
            ->where('store_id', $storeId)
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();



        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $data->pluck('total'),
                ],
            ],
            'labels' => $data->pluck('tanggal'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    protected int|string|array $columnSpan = 'full';
}
