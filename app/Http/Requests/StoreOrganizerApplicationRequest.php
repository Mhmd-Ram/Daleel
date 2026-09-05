<?php

namespace App\Http\Requests;

use App\Models\OrganizerApplication;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizerApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Organizers have nothing to apply for, and a user may only have one
     * application open at a time.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && ! $user->isOrganizer()
            && $user->pendingOrganizerApplication() === null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => [
                'required',
                'string',
                'min:'.OrganizerApplication::MIN_MESSAGE_LENGTH,
                'max:'.OrganizerApplication::MAX_MESSAGE_LENGTH,
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.min' => __('app.profile.application_too_short', [
                'min' => OrganizerApplication::MIN_MESSAGE_LENGTH,
            ]),
        ];
    }
}
