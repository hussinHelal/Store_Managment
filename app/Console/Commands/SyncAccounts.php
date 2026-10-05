<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncAccounts extends Command
{
    protected $signature = 'app:sync-accounts';

    protected $description = 'Create or update the two built-in accounts from configured credentials';

    public function handle(): int
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'username')
            || !Schema::hasColumn('users', 'system_account')) {
            $this->error('User account columns are missing. Run the database migrations first.');

            return self::FAILURE;
        }

        $accounts = config('accounts');
        $superadmin = $accounts['superadmin'] ?? [];
        $admin = $accounts['admin'] ?? [];

        foreach (['superadmin' => $superadmin, 'admin' => $admin] as $type => $account) {
            $username = strtolower(trim((string) ($account['username'] ?? '')));
            $password = (string) ($account['password'] ?? '');

            if ($username === '' || $password === '') {
                $this->error("{$type} username and password must be configured.");

                return self::FAILURE;
            }

            if (!preg_match('/^[A-Za-z0-9._-]{3,80}$/', $username)) {
                $this->error("{$type} username must be 3-80 characters using letters, numbers, dots, underscores, or hyphens.");

                return self::FAILURE;
            }

            if (mb_strlen($password) < 10) {
                $this->error("{$type} password must be at least 10 characters.");

                return self::FAILURE;
            }
        }

        if (Str::lower($superadmin['username']) === Str::lower($admin['username'])) {
            $this->error('Superadmin and admin usernames must be different.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($superadmin, $admin): void {
                $pagePermissions = [];

                foreach (array_keys(config('access.pages', [])) as $page) {
                    foreach (['view', 'manage'] as $ability) {
                        $pagePermissions[] = Permission::firstOrCreate([
                            'name' => "page.{$page}.{$ability}",
                            'guard_name' => 'web',
                        ]);
                    }
                }

                $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
                $adminRole->syncPermissions($pagePermissions);

                $superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
                $superadminRole->syncPermissions($pagePermissions);

                $this->syncAccount('superadmin', $superadmin, $superadminRole);
                $this->syncAccount('admin', $admin, $adminRole);
            });
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Account synchronization failed. Check the application log for details.');

            return self::FAILURE;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->info('Built-in accounts synchronized. Credentials were not displayed.');

        return self::SUCCESS;
    }

    private function syncAccount(string $type, array $config, Role $role): void
    {
        $username = strtolower(trim($config['username']));
        $user = User::query()->where('system_account', $type)->first()
            ?? User::query()->where('username', $username)->first();

        $conflict = User::query()->where('username', $username)
            ->when($user, fn ($query) => $query->whereKeyNot($user->getKey()))
            ->exists();

        if ($conflict) {
            throw new \RuntimeException("The configured {$type} username is already assigned to another account.");
        }

        $user ??= new User();
        $passwordChanged = !$user->exists || !Hash::check($config['password'], $user->getAuthPassword());

        $user->forceFill([
            'username' => $username,
            'system_account' => $type,
            'name' => trim((string) ($config['name'] ?? '')) ?: ucfirst($type),
            'email' => $user->email ?: $this->availableAccountEmail($username, $user),
            'role' => $type === 'superadmin' ? 'superadmin' : 'admin',
            'is_active' => true,
        ]);

        if ($passwordChanged) {
            $user->password = $config['password'];
        }

        $user->save();
        $user->syncRoles([$role]);

        if ($passwordChanged) {
            $user->tokens()->delete();
        }
    }

    private function availableAccountEmail(string $username, User $user): string
    {
        $base = Str::lower($username).'@accounts.local';
        $email = $base;
        $suffix = 1;

        while (User::query()->where('email', $email)
            ->when($user->exists, fn ($query) => $query->whereKeyNot($user->getKey()))
            ->exists()) {
            $email = Str::before($base, '@').'-'.$suffix++.'@accounts.local';
        }

        return $email;
    }
}
