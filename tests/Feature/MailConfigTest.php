<?php

use Illuminate\Support\Facades\Mail;

it('rejects an argument that is not an email address', function () {
    $this->artisan('mail:test', ['recipient' => 'not-an-email'])
        ->expectsOutputToContain('Not an email address')
        ->assertExitCode(1);
});

it('sends a test message through the configured mailer', function () {
    Mail::fake();

    $this->artisan('mail:test', ['recipient' => 'someone@example.com'])
        ->assertExitCode(0);
});

it('warns that the log mailer delivers nothing', function () {
    config(['mail.default' => 'log']);
    Mail::fake();

    $this->artisan('mail:test', ['recipient' => 'someone@example.com'])
        ->expectsOutputToContain('only writes to the log file')
        ->assertExitCode(0);
});
