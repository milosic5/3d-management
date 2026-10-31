<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('maintenance')->printer);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'maintenance_month' => 'required|date_format:Y-m',
            'working_hours_at_maintenance' => 'required|integer|min:0',
            'hours_printed_this_month' => 'required|integer|min:0',
            'lubricated' => 'required|boolean',
        ];
    }
}
