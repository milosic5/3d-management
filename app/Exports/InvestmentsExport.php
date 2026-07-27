<?php

namespace App\Exports;

use App\Models\Investment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvestmentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Investment::with('category');

        if (!empty($this->filters['category'])) {
            $query->where('category_id', $this->filters['category']);
        }
        if (!empty($this->filters['from'])) {
            $query->whereDate('invested_at', '>=', $this->filters['from']);
        }
        if (!empty($this->filters['to'])) {
            $query->whereDate('invested_at', '<=', $this->filters['to']);
        }

        return $query->latest('invested_at')->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Category',
            'Name',
            'Quantity',
            'Unit',
            'Unit Cost',
            'Total Amount',
            'Notes',
        ];
    }

    public function map($inv): array
    {
        return [
            $inv->invested_at?->format('Y-m-d'),
            $inv->category?->name ?? '—',
            $inv->name,
            $inv->quantity,
            $inv->unit,
            number_format((float) $inv->unit_cost, 4),
            number_format((float) $inv->amount, 2),
            $inv->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
