<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Command
{
    protected $signature = 'app:reset-password {--password=admin123}';

    protected $description = 'Reset the admin user password';

    public function handle(): int
    {
        $password = $this->option('password');
        $hashed = Hash::make($password);

        DB::table('wp_users')->where('ID', 1)->update(['user_pass' => $hashed]);

        $this->info('Admin password reset successfully!');
        $this->warn("New password: $password");
        $this->info('Login: '.rtrim(config('app.url'), '/').'/admin');
        $this->info('Username: q');

        return self::SUCCESS;
    }
}
