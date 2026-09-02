<?php

namespace App\Http\Requests;

use App\Enums\LibyanCity;
use App\Http\Requests\Concerns\ValidatesLibyanPhoneNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    use ValidatesLibyanPhoneNumbers;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone_number' => [...$this->phoneNumberRules(), Rule::unique('users', 'phone_number')->ignore($userId)],
            'dob' => ['required', 'date', 'before:today'],
            'location' => ['required', Rule::enum(LibyanCity::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->phoneNumberMessages();
    }
}
