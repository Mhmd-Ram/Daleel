<?php

namespace App\Http\Requests\Concerns;

/**
 * One definition of a valid Libyan mobile number, shared by sign-up and profile
 * edit so the two can never drift apart (SRS FR-2.2, UC1 step 3).
 */
trait ValidatesLibyanPhoneNumbers
{
    /**
     * Libyan mobile numbers: +218 or a leading 0, then a 9 and one of the five
     * operator digits, then seven more.
     */
    private const PHONE_PATTERN = '/^(?:\+218|0)9[1-5]\d{7}$/';

    /**
     * @return array<int, string>
     */
    protected function phoneNumberRules(): array
    {
        return ['required', 'string', 'regex:'.self::PHONE_PATTERN];
    }

    /**
     * @return array<string, string>
     */
    protected function phoneNumberMessages(): array
    {
        return [
            'phone_number.regex' => 'Enter a Libyan mobile number, for example +218911234567 or 0911234567.',
        ];
    }
}
