<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return self::orderRules();
    }

    /**
     * Rules for a full order (details + line items), shared by create and edit.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function orderRules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'estimated_print_minutes' => 'nullable|integer',
            'created_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'items.*.filament_id' => 'nullable|exists:filaments,id',
            'items.*.print_cost' => 'nullable|numeric|min:0',
            'items.*.print_time_minutes' => 'nullable|integer|min:0',
            'items.*.color_name' => 'nullable|string',
            'items.*.color_hex' => 'nullable|string|size:7',
            'items.*.weight_grams' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ];
    }
}
