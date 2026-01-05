<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleSeeder extends Seeder
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
        $permissionModel = config('permission.models.permission', \Spatie\Permission\Models\Permission::class);

        $roleData = [
            'id' => 1,
            'guard_name' => 'web',
            'name' => 'Admin',
        ];

        $user_role = $roleModel::find((int) $roleData['id']);

        if (!$user_role) {
            $user_role = $roleModel::create([
                'id' => 1,
                'guard_name' => $roleData['guard_name'],
                'name' => $roleData['name'],
            ]);
        } else {
            $user_role->guard_name = $roleData['guard_name'];
            $user_role->name = $roleData['name'];
            $user_role->save();
        }

        // Assign all permissions to Admin role
        $user_role->syncPermissions($permissionModel::where('guard_name', 'web')->pluck('name'));

        $this->command->info('Admin role seeded successfully with all permissions!');
    }
}

