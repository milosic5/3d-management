<?php

namespace App\Http\Requests;

use App\Models\Packaging;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePackagingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Packaging::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string|in:box,envelope',
            'name' => 'nullable|string|max:255',
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ];
    }
}
