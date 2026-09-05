<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * An admin's site-mode account is refused outright: it is not theirs to
     * delete, it would orphan their identity mid-session, and the cascade would
     * take their saved events with it. Checked here rather than in the
     * controller so it decides before the password rule gets a say.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ! $user->isStaffAccount();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Re-entering the password is the confirmation step UC4 asks for. The
     * deletion cascades well beyond this row, so a stray click must not be
     * enough to trigger it.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}
