<?php

namespace App\Http\Requests;

use App\Enums\LibyanCity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            // Structured alongside the free-text address, because the public
            // listing filters on it (FR-4.5) and free text cannot back a dropdown.
            'city' => ['required', Rule::enum(LibyanCity::class)],
            // The map pin is optional, but a lone coordinate is meaningless, so
            // each half requires the other. Ranges are the real world's.
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'category_id' => ['required', 'exists:categories,id'],
            'start_date_time' => ['required', 'date'],
            // End must be after the start.
            'end_date_time' => ['required', 'date', 'after:start_date_time'],
            // Free (0) or a positive amount; spelling kept from the brief.
            'tiket_cost' => ['required', 'numeric', 'min:0'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // An unchecked checkbox is absent from the request; normalise to a boolean.
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
