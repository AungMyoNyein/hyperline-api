<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdministrator extends Command
{
    protected $signature = 'hyperline:admin
                            {email : Administrator email address}
                            {name=HyperLine Admin : Display name}
                            {--password= : Password; falls back to HYPERLINE_INITIAL_ADMIN_PASSWORD}';

    protected $description = 'Create or reset the HyperLine CMS administrator and grant the admin role';

    public function handle(): int
    {
        $password = trim((string) ($this->option('password') ?: config('ott.admin.initial_password')));

        if ($password === '') {
            $this->error('No password supplied. Pass --password or set HYPERLINE_INITIAL_ADMIN_PASSWORD.');

            return self::FAILURE;
        }

        $role = Role::firstOrCreate(['slug' => Role::ADMIN], ['name' => 'Administrator']);

        $user = User::firstOrNew(['email' => $this->argument('email')]);
        $user->name = $this->argument('name') ?: 'HyperLine Admin';
        $user->password = Hash::make($password);

        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
        }

        $user->save();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $this->info("Administrator {$user->email} is ready.");
        $this->warn('Remove HYPERLINE_INITIAL_ADMIN_PASSWORD from .env, then recreate the api, worker and scheduler containers.');

        return self::SUCCESS;
    }
}
