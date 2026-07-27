<?php

namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use App\Exports\InvestmentsExport;
use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export orders as Excel (.xlsx).
     */
    public function ordersExcel(Request $request)
    {
        $filters = $request->only(['status', 'from', 'to']);
        $filename = 'orders_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new OrdersExport($filters), $filename);
    }

    /**
     * Export investments as Excel (.xlsx).
     */
    public function investmentsExcel(Request $request)
    {
        $filters = $request->only(['category', 'from', 'to']);
        $filename = 'investments_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new InvestmentsExport($filters), $filename);
    }

    /**
     * Export a single order as PDF.
     */
    public function orderPdf(Order $order)
    {
        $order->load(['items.product', 'creator']);

        $pdf = Pdf::loadView('pdf.order', compact('order'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("order_{$order->order_number}.pdf");
    }
}
