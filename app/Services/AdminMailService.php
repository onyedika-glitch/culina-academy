<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminMailService
{
    public static function adminEmail(): string
    {
        $custom = trim((string) DB::table('wp_options')->where('option_name', 'ca_admin_email')->value('option_value'));

        if ($custom !== '') {
            return $custom;
        }

        $admin = trim((string) DB::table('wp_options')->where('option_name', 'admin_email')->value('option_value'));

        return $admin !== '' ? $admin : (string) config('mail.from.address', 'admin@example.com');
    }

    public static function send(string $mailableClass, mixed ...$args): bool
    {
        try {
            Mail::to(static::adminEmail())->send(new $mailableClass(...$args));

            return true;
        } catch (\Throwable $e) {
            Log::error('Mail send failed: ' . $e->getMessage(), [
                'mailable' => $mailableClass,
            ]);

            return false;
        }
    }
}