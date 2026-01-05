<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        // Get role model from config
        $roleModel = config('permission.models.role', \Spatie\Permission\Models\Role::class);

        // Create or get Admin role
        $adminRole = $roleModel::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        // Create or update Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@mail.com',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
            ]
        );

        // Update admin details if user already exists
        if ($admin->wasRecentlyCreated === false) {
            $admin->name = 'Admin';
            $admin->password = Hash::make('123456');
            $admin->save();
        }

        // Assign Admin role if not already assigned
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole($adminRole);
        }

        $this->command->info('Admin user seeded successfully!');
        $this->command->info('Email: admin@mail.com');
        $this->command->info('Password: 123456');
    }
}
