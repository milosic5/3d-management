<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Order::with(['items.product', 'creator']);

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['from'])) {
            $query->whereDate('created_at', '>=', $this->filters['from']);
        }
        if (!empty($this->filters['to'])) {
            $query->whereDate('created_at', '<=', $this->filters['to']);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Order #',
            'Customer',
            'Status',
            'Products',
            'Items (qty)',
            'Total Price',
            'Filament Used (g)',
            'Print Time (min)',
            'Created At',
            'Created By',
            'Notes',
        ];
    }

    public function map($order): array
    {
        $products    = $order->items->map(fn($i) => $i->product?->name ?? '—')->implode(', ');
        $totalQty    = $order->items->sum('quantity');
        $totalGrams  = $order->items->sum(fn($i) => $i->weight_grams * $i->quantity);
        $totalMins   = $order->items->sum(fn($i) => ($i->print_time_minutes ?? 0) * $i->quantity);

        return [
            $order->order_number,
            $order->customer_name,
            strtoupper($order->status),
            $products,
            $totalQty,
            number_format((float) $order->total_price, 2),
            round($totalGrams, 2),
            $totalMins,
            $order->created_at?->format('Y-m-d H:i'),
            $order->creator?->name ?? '—',
            $order->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
