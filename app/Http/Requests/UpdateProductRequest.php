<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight_grams' => 'required|numeric',
            'print_time_minutes' => 'required|integer',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:5120',
            'model_file' => 'nullable|file|max:102400',
            'model_file_2' => 'nullable|file|max:102400',
            'is_active' => 'boolean',
        ];
    }
}
