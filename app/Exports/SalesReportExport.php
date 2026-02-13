<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Database\Eloquent\Builder;

class SalesReportExport implements FromQuery, WithMapping, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $query;

    public function __construct(Builder $query)
    {
        // Penting: Hilangkan select, eager loading, atau pengurutan yang bisa merusak query
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['Tanggal', 'No. Invoice', 'Pelanggan', 'Metode', 'Total'];
    }

    public function map($order): array
    {
        return [
            $order->created_at->format('d/m/Y H:i'),
            $order->invoice_number,
            $order->customer_name ?? 'Umum',
            strtoupper($order->payment_method),
            $order->total_price,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        return [
            1 => ['font' => ['bold' => true]], // Header Bold
            "A1:{$lastCol}{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }
}
