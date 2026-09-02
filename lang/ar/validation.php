<?php

/*
|--------------------------------------------------------------------------
| Arabic validation messages
|--------------------------------------------------------------------------
|
| Only the rules this application actually uses are translated. Everything
| else falls back to English on purpose: a bad machine translation of a rule
| nobody triggers is worse than a clear English one.
|
| Numbers stay in Latin digits here, matching the rest of the interface.
|
*/

return [
    'accepted' => 'يجب قبول :attribute.',
    'after' => 'يجب أن يكون :attribute تاريخًا بعد :date.',
    'before' => 'يجب أن يكون :attribute تاريخًا قبل :date.',
    'between' => [
        'array' => 'يجب أن يحتوي :attribute على ما بين :min و :max عنصرًا.',
        'file' => 'يجب أن يكون حجم :attribute بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'string' => 'يجب أن يكون طول :attribute بين :min و :max حرفًا.',
    ],
    'boolean' => 'يجب أن تكون قيمة :attribute صحيحة أو خاطئة.',
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'يجب أن يكون :attribute تاريخًا صالحًا.',
    'email' => 'يجب أن يكون :attribute بريدًا إلكترونيًا صالحًا.',
    'enum' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'exists' => 'القيمة المحددة لـ :attribute غير موجودة.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'max' => [
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :max عنصرًا.',
        'file' => 'يجب ألا يزيد حجم :attribute عن :max كيلوبايت.',
        'numeric' => 'يجب ألا تزيد قيمة :attribute عن :max.',
        'string' => 'يجب ألا يزيد طول :attribute عن :max حرفًا.',
    ],
    'min' => [
        'array' => 'يجب أن يحتوي :attribute على :min عنصرًا على الأقل.',
        'file' => 'يجب ألا يقل حجم :attribute عن :min كيلوبايت.',
        'numeric' => 'يجب ألا تقل قيمة :attribute عن :min.',
        'string' => 'يجب ألا يقل طول :attribute عن :min حرفًا.',
    ],
    'numeric' => 'يجب أن يكون :attribute رقمًا.',
    'regex' => 'صيغة :attribute غير صالحة.',
    'required' => 'حقل :attribute مطلوب.',
    'string' => 'يجب أن يكون :attribute نصًا.',
    'unique' => ':attribute مستخدم من قبل.',

    /*
    |--------------------------------------------------------------------------
    | Custom messages
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'phone_number' => [
            'regex' => 'أدخل رقم هاتف ليبي، مثل +218911234567 أو 0911234567.',
        ],
        'current_password' => [
            'current_password' => 'كلمة المرور الحالية غير صحيحة.',
        ],
        'password' => [
            'current_password' => 'أدخل كلمة المرور الحالية للتأكيد.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Attribute names
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'current_password' => 'كلمة المرور الحالية',
        'phone_number' => 'رقم الهاتف',
        'dob' => 'تاريخ الميلاد',
        'location' => 'المدينة',
        'city' => 'المدينة',
        'description' => 'الوصف',
        'category_id' => 'التصنيف',
        'start_date_time' => 'وقت البداية',
        'end_date_time' => 'وقت النهاية',
        'tiket_cost' => 'سعر التذكرة',
        'max_capacity' => 'الحد الأقصى للحضور',
        'reason' => 'السبب',
        'message' => 'الرسالة',
    ],
];
