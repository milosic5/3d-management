<?php

namespace App\Http\Requests;

use App\Models\Investment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvestmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Investment::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:investment_categories,id',
            'filament_id' => 'nullable|exists:filaments,id',
            'name' => 'required|string|max:255',
            'unit_cost' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'invested_at' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }
}
