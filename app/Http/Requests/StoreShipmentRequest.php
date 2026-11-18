<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', Rule::exists('users', 'id')],
            'carrier_id' => ['required', Rule::exists('carriers', 'id')->where('is_active', true)],
            'origin_id' => ['required', 'exists:locations,id'],
            'destination_id' => ['required', 'exists:locations,id', 'different:origin_id'],
            'cargo_type' => ['nullable', 'string', 'max:100'],
            'weight_kg' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'volume_cbm' => ['nullable', 'numeric', 'min:0'],
            'expected_departure' => ['nullable', 'date'],
            'expected_departure_at' => ['nullable', 'date'],
            'expected_arrival' => ['nullable', 'date'],
            'expected_arrival_at' => ['nullable', 'date'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'in:IDR,USD'],
            'items' => ['nullable', 'array'],
            'items.*.cargo_type' => ['nullable', 'string'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.weight' => ['nullable', 'numeric'],
            'items.*.weight_kg' => ['nullable', 'numeric'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (isset($data['expected_departure']) && !isset($data['expected_departure_at'])) {
            $data['expected_departure_at'] = $data['expected_departure'];
        }
        if (isset($data['expected_arrival']) && !isset($data['expected_arrival_at'])) {
            $data['expected_arrival_at'] = $data['expected_arrival'];
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'destination_id.different' => 'Origin and destination must be different locations.',
            'expected_arrival_at.after' => 'Expected arrival must be after expected departure.',
        ];
    }
}
