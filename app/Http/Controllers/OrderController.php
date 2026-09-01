<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Filament;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items.product', 'creator']);

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('items.product', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->boolean('show_cancelled')) {
            $query->where('status', 'cancelled');
        } else {
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            } else {
                $query->where('status', '!=', 'cancelled');
            }
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($sort = $request->input('sort')) {
            $dir = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
            if (in_array($sort, ['order_number', 'total_price', 'created_at'])) {
                $query->orderBy($sort, $dir);
            }
        } else {
            $query->latest();
        }

        return Inertia::render('Orders/Index', [
            'orders' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'from', 'to', 'sort', 'direction', 'show_cancelled'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Orders/Create', [
            'products' => Product::where('is_active', true)->select('id', 'name', 'color_hex', 'price', 'print_time_minutes', 'weight_grams')->latest()->get(),
            'filaments' => Filament::select('id', 'brand', 'name', 'color_name', 'color_hex', 'price_per_kg')->get()
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Order::class);

        $validated = $this->validateOrderRequest($request);

        DB::transaction(function () use ($validated, $request) {
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'notes'         => $validated['notes'],
                'status'        => $validated['status'],
                'estimated_print_minutes' => $validated['estimated_print_minutes'],
                'created_by'    => $request->user()->id,
                'created_at'    => $validated['created_at'] ?? now(),
                'total_price'   => 0,
            ]);

            [$totalPrice, $totalMinutes] = $this->syncOrderItems($order, $validated['items']);

            $order->update([
                'total_price'             => $totalPrice,
                'estimated_print_minutes' => $validated['estimated_print_minutes'] ?? $totalMinutes,
            ]);

            // Log creation
            OrderHistory::create([
                'order_id'  => $order->id,
                'user_id'   => $request->user()->id,
                'action'    => 'created',
                'to_status' => $order->status,
            ]);
        });

        return redirect()->route('orders.index');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return Inertia::render('Orders/Show', [
            'order' => $order->load(['items.product.filament', 'items.filament', 'creator', 'updater', 'history'])
        ]);
    }

    public function edit(Order $order)
    {
        $this->authorize('update', $order);

        return Inertia::render('Orders/Edit', [
            'order'     => $order->load('items'),
            'products'  => Product::where('is_active', true)->select('id', 'name', 'color_hex', 'price', 'print_time_minutes', 'weight_grams')->latest()->get(),
            'filaments' => Filament::select('id', 'brand', 'name', 'color_name', 'color_hex', 'price_per_kg')->get()
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        if ($request->has('items')) {
            $validated = $this->validateOrderRequest($request);

            DB::transaction(function () use ($validated, $request, $order) {
                $order->update([
                    'customer_name'           => $validated['customer_name'],
                    'notes'                   => $validated['notes'],
                    'status'                  => $validated['status'],
                    'estimated_print_minutes' => $validated['estimated_print_minutes'],
                    'updated_by'              => $request->user()->id,
                    'created_at'              => $validated['created_at'] ?? $order->created_at,
                ]);

                $order->items()->delete();

                [$totalPrice, $totalMinutes] = $this->syncOrderItems($order, $validated['items']);

                $order->update([
                    'total_price'             => $totalPrice,
                    'estimated_print_minutes' => $validated['estimated_print_minutes'] ?? $totalMinutes,
                ]);

                // Log update
                OrderHistory::create([
                    'order_id'    => $order->id,
                    'user_id'     => $request->user()->id,
                    'action'      => 'updated',
                    'from_status' => $order->getOriginal('status'),
                    'to_status'   => $validated['status'],
                ]);
            });

            return redirect()->route('orders.index');
        }

        $validated = $request->validate([
            'status' => 'required|in:received,printing,finished,delivered,cancelled',
        ]);

        $oldStatus = $order->status;

        $order->update([
            'status'     => $validated['status'],
            'updated_by' => $request->user()->id,
        ]);

        // Log status change
        if ($oldStatus !== $validated['status']) {
            OrderHistory::create([
                'order_id'    => $order->id,
                'user_id'     => $request->user()->id,
                'action'      => 'status_changed',
                'from_status' => $oldStatus,
                'to_status'   => $validated['status'],
            ]);
        }

        return back();
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);
        $order->delete();

        return redirect()->route('orders.index');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate the full order request (shared between store and update).
     */
    private function validateOrderRequest(Request $request): array
    {
        return $request->validate([
            'customer_name'              => 'required|string|max:255',
            'notes'                      => 'nullable|string',
            'status'                     => 'required|in:received,printing,finished,delivered,cancelled',
            'estimated_print_minutes'    => 'nullable|integer',
            'created_at'                 => 'nullable|date',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.quantity'           => 'required|integer|min:1',
            'items.*.unit_price'         => 'required|numeric',
            'items.*.filament_id'        => 'nullable|exists:filaments,id',
            'items.*.print_cost'         => 'nullable|numeric|min:0',
            'items.*.print_time_minutes' => 'nullable|integer|min:0',
            'items.*.color_name'         => 'nullable|string',
            'items.*.color_hex'          => 'nullable|string|size:7',
            'items.*.weight_grams'       => 'nullable|numeric|min:0',
            'items.*.notes'              => 'nullable|string',
        ]);
    }

    /**
     * Persist order items and return [$totalPrice, $totalMinutes].
     */
    private function syncOrderItems(Order $order, array $items): array
    {
        $totalPrice   = 0;
        $totalMinutes = 0;

        foreach ($items as $item) {
            $product  = Product::find($item['product_id']);
            $filament = !empty($item['filament_id']) ? Filament::find($item['filament_id']) : null;

            $order->items()->create([
                'product_id'         => $product->id,
                'filament_id'        => $item['filament_id'] ?? null,
                'quantity'           => $item['quantity'],
                'unit_price'         => $item['unit_price'],
                'print_cost'         => $item['print_cost'] ?? 0,
                'print_time_minutes' => $item['print_time_minutes'] ?? $product->print_time_minutes,
                'weight_grams'       => $item['weight_grams'] ?? $product->weight_grams,
                'color_name'         => $filament ? $filament->color_name : ($item['color_name'] ?? $product->color_name),
                'color_hex'          => $filament ? $filament->color_hex  : ($item['color_hex']  ?? $product->color_hex),
                'notes'              => $item['notes'] ?? null,
            ]);

            $totalPrice   += ($item['unit_price'] * $item['quantity']);
            $totalMinutes += ($item['print_time_minutes'] ?? $product->print_time_minutes ?? 0);
        }

        return [$totalPrice, $totalMinutes];
    }
}
