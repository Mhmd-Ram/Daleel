<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hourly rather than daily so an event saved a few hours before it starts still
// gets its reminder. The command's `reminder_sent` flag keeps this idempotent.
Schedule::command('events:send-reminders')->hourly();
