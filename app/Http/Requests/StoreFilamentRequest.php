<?php

namespace App\Http\Requests;

use App\Enums\FilamentMaterial;
use App\Models\Filament;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFilamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Filament::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand' => 'required|string|max:255',
            'name' => ['required', 'string', 'max:255', Rule::unique('filaments', 'name')->ignore($this->route('filament'))],
            'material' => ['required', Rule::enum(FilamentMaterial::class)],
            'color_name' => 'required|string|max:255',
            'color_hex' => 'required|string|size:7',
            'price_per_kg' => 'required|numeric|min:0',
            'empty_spool_weight_grams' => 'nullable|numeric|min:0|max:9999',
            'notes' => 'nullable|string',
        ];
    }
}
