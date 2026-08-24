<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageVehicles() ?? false;
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'number_plate' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'number_plate')->ignore($vehicleId)],
            'engine_capacity' => ['required', 'string', 'max:50'],
            'make' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::in(Vehicle::CATEGORIES)],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
