<?php

namespace App\Http\Requests;

use App\Models\Device;
use Illuminate\Foundation\Http\FormRequest;

class DeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public ?string $name;
    public ?int $building_id;
    public ?string $type;
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:devices,name',
            'building_id' => 'required|integer|exists:buildings,id',
            'type' => 'required|string|in:in,out',
        ];
    }
}
