<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles both the full edit form (with items) and the quick status change from lists.
 */
class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('order'));
    }

    public function isFullUpdate(): bool
    {
        return $this->has('items');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isFullUpdate()) {
            return StoreOrderRequest::orderRules();
        }

        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ];
    }
}
