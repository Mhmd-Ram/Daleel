<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Send one message through the configured mailer and report what happened.
 *
 * Both real mailables are `ShouldQueue`, so a misconfigured SMTP account fails
 * inside the queue worker where nobody is looking. This sends synchronously and
 * prints the transport's own error, which is the only way to tell "the app is
 * fine, the worker is not running" apart from "Gmail rejected the credentials".
 */
class MailTest extends Command
{
    protected $signature = 'mail:test {recipient : Where to send the test message}';

    protected $description = 'Send a test email through the configured mailer and report the result';

    public function handle(): int
    {
        $recipient = $this->argument('recipient');

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error("Not an email address: {$recipient}");

            return self::FAILURE;
        }

        $mailer = config('mail.default');

        $this->reportConfiguration($mailer);

        try {
            Mail::raw(
                'Test message from '.config('app.name').' sent at '.now()->toDateTimeString().'.',
                fn ($message) => $message->to($recipient)->subject(config('app.name').' mail test'),
            );
        } catch (TransportExceptionInterface $e) {
            $this->error('The mailer refused the message:');
            $this->line($e->getMessage());
            $this->newLine();
            $this->line('For Gmail, check that 2-Step Verification is on, that MAIL_PASSWORD is a');
            $this->line('16-character app password with the spaces removed, and that MAIL_FROM_ADDRESS');
            $this->line('is the authenticated account or a verified alias.');

            return self::FAILURE;
        }

        $this->info("Sent to {$recipient} via the [{$mailer}] mailer.");

        return self::SUCCESS;
    }

    /**
     * Print the settings the message will actually be sent with.
     *
     * Printed before the attempt, so a failure below is read next to the
     * configuration that caused it.
     */
    private function reportConfiguration(string $mailer): void
    {
        $this->table(['Setting', 'Value'], [
            ['Mailer', $mailer],
            ['Host', config("mail.mailers.{$mailer}.host") ?? '-'],
            ['Port', config("mail.mailers.{$mailer}.port") ?? '-'],
            ['Scheme', config("mail.mailers.{$mailer}.scheme") ?? '-'],
            ['Username', config("mail.mailers.{$mailer}.username") ?? '-'],
            ['From', config('mail.from.address')],
        ]);

        if ($mailer === 'log') {
            $this->warn('The `log` mailer only writes to the log file. Nothing will be delivered.');
        }
    }
}
