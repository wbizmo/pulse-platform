<?php

namespace App\Console\Commands;

use App\Models\Installation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class PulseInstall extends Command
{
    protected $signature = 'pulse:install';

    protected $description = 'Securely install Pulse and create its first super administrator';

    public function handle(): int
    {
        if ($this->call('pulse:preflight') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (Schema::hasTable('installations') && Installation::query()->exists()) {
            $this->error('Pulse is already installed. Re-entry is not permitted.');

            return self::FAILURE;
        }

        $name = $this->ask('Administrator name');
        $email = $this->ask('Administrator email');
        $password = $this->secret('Administrator password (minimum 12 characters, mixed case, number and symbol)');
        $confirmation = $this->secret('Confirm administrator password');
        $validator = Validator::make(compact('name', 'email', 'password', 'confirmation'), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'same:confirmation', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        Artisan::call('migrate', ['--force' => true], $this->output);
        Artisan::call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true], $this->output);

        try {
            DB::transaction(function () use ($name, $email, $password): void {
                if (Installation::query()->lockForUpdate()->exists()) {
                    throw new \LogicException('Pulse was installed by another process.');
                }
                $role = Role::query()->where('name', 'super_admin')->firstOrFail();
                $user = User::query()->create([
                    'name' => $name,
                    'email' => mb_strtolower($email),
                    'password' => Hash::make($password),
                    'role' => 'super_admin',
                    'status' => 'active',
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
                $user->roles()->sync([$role->id]);
                Installation::query()->create([
                    'release' => config('app.version', '11'),
                    'installed_by' => $user->id,
                    'completed_at' => now(),
                ]);
            });
        } catch (\Throwable $exception) {
            $this->error('Installation did not complete: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Pulse installation completed. Sign in and enroll MFA before using privileged capabilities.');

        return self::SUCCESS;
    }
}
