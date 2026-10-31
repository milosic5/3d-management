<?php

namespace App\Http\Requests;

use App\Models\Investment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvestmentCategoryRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'type' => 'required|in:equipment,consumable,utility,other',
        ];
    }
}
