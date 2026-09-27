<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('atu:make-admin {email} {--name=ATU Administrator}')]
#[Description('Create or promote an account to ATU administrator')]
class CreateAdminAccount extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        $password = Str::password(20);
        $user = User::firstOrNew(['email' => $email]);
        $created = ! $user->exists;

        if ($created) {
            $user->name = (string) $this->option('name');
            $user->phone = '';
            $user->password = $password;
        }

        $user->role = 'admin';
        $user->is_active = true;
        $user->save();

        $this->info($created ? "Admin account created: {$email}" : "Account promoted to admin: {$email}");
        if ($created) {
            $this->warn("Temporary password (shown once): {$password}");
            $this->line('Sign in and change this password immediately.');
        } else {
            $this->line('Existing password retained.');
        }

        return self::SUCCESS;
    }
}
