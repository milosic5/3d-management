<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
        .header { background: #0f1117; color: #fff; padding: 24px 32px; display: flex; align-items: center; }
        .logo-dot { width: 10px; height: 10px; background: #f97316; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .logo-text { font-size: 18px; font-weight: bold; letter-spacing: -0.5px; }
        .title-block { padding: 24px 32px 12px; border-bottom: 2px solid #f97316; }
        .order-number { font-size: 22px; font-weight: bold; color: #f97316; font-family: monospace; }
        .order-meta { font-size: 11px; color: #64748b; margin-top: 4px; }
        .section { padding: 20px 32px; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; }
        .info-grid { display: flex; gap: 40px; }
        .info-group { flex: 1; }
        .info-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .info-value { font-size: 13px; font-weight: 600; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; }
        thead th { padding: 10px 12px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:last-child { border-bottom: none; }
        tbody td { padding: 10px 12px; font-size: 12px; }
        .text-right { text-align: right; }
        .mono { font-family: monospace; }
        .totals { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px; padding: 16px 20px; margin: 0 32px 24px; }
        .totals-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px; color: #64748b; }
        .totals-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; color: #ea580c; margin-top: 8px; padding-top: 8px; border-top: 1px solid #fed7aa; font-family: monospace; }
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-received  { background: #f1f5f9; color: #475569; }
        .status-printing  { background: #dbeafe; color: #1d4ed8; }
        .status-finished  { background: #fed7aa; color: #c2410c; }
        .status-delivered { background: #dcfce7; color: #16a34a; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .footer { text-align: center; font-size: 10px; color: #94a3b8; padding: 16px 32px; border-top: 1px solid #f1f5f9; margin-top: 8px; }
        .notes-box { background: #fafafa; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 14px; font-size: 12px; color: #475569; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <span class="logo-dot"></span>
        <span class="logo-text">3D PrintShop</span>
    </div>

    <div class="title-block">
        <div class="order-number">{{ $order->order_number }}</div>
        <div class="order-meta">
            Generated on {{ now()->format('Y-m-d H:i') }}
            &nbsp;·&nbsp;
            <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Order Details</div>
        <div class="info-grid">
            <div class="info-group">
                <div class="info-label">Customer</div>
                <div class="info-value">{{ $order->customer_name }}</div>
            </div>
            <div class="info-group">
                <div class="info-label">Order Date</div>
                <div class="info-value">{{ $order->created_at?->format('Y-m-d') }}</div>
            </div>
            <div class="info-group">
                <div class="info-label">Created By</div>
                <div class="info-value">{{ $order->creator?->name ?? '—' }}</div>
            </div>
            <div class="info-group">
                <div class="info-label">Est. Print Time</div>
                @php
                    $h = intdiv($order->estimated_print_minutes ?? 0, 60);
                    $m = ($order->estimated_print_minutes ?? 0) % 60;
                @endphp
                <div class="info-value">{{ $h > 0 ? $h . 'h ' : '' }}{{ $m }}m</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Items</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Material</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Weight (g)</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $item->product?->name ?? '—' }}</div>
                        @if($item->color_name)
                            <div style="font-size:10px;color:#94a3b8">{{ $item->color_name }}</div>
                        @endif
                        @if($item->notes)
                            <div style="font-size:10px;color:#f97316;margin-top:2px">Note: {{ $item->notes }}</div>
                        @endif
                    </td>
                    <td style="color:#64748b;font-size:11px">{{ strtoupper($item->product?->material ?? '—') }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right mono">{{ number_format((float)$item->unit_price, 2) }}</td>
                    <td class="text-right mono">{{ number_format((float)$item->weight_grams * $item->quantity, 1) }}</td>
                    <td class="text-right mono" style="font-weight:600">{{ number_format((float)$item->unit_price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($order->notes)
    <div class="section" style="padding-top:0">
        <div class="section-title">Notes</div>
        <div class="notes-box">{{ $order->notes }}</div>
    </div>
    @endif

    <div class="totals">
        <div class="totals-row">
            <span>Total Items (qty)</span>
            <span>{{ $order->items->sum('quantity') }}</span>
        </div>
        <div class="totals-row">
            <span>Total Filament Used</span>
            <span>{{ number_format($order->items->sum(fn($i) => $i->weight_grams * $i->quantity), 1) }}g</span>
        </div>
        <div class="totals-total">
            <span>TOTAL</span>
            <span>{{ number_format((float)$order->total_price, 2) }}</span>
        </div>
    </div>

    <div class="footer">
        3D PrintShop · {{ $order->order_number }} · Thank you!
    </div>
</body>
</html>
