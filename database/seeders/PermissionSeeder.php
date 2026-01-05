<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
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

        // Define permissions with modules
        $permissions = [
            // Dashboard
            ['name' => 'View Dashboard', 'module' => 'Dashboard'],

            // Users
            ['name' => 'View Users', 'module' => 'Users'],
            ['name' => 'Create Users', 'module' => 'Users'],
            ['name' => 'Edit Users', 'module' => 'Users'],
            ['name' => 'Delete Users', 'module' => 'Users'],
            ['name' => 'Change Users Status', 'module' => 'Users'],
            ['name' => 'Update Users Password', 'module' => 'Users'],

            // Roles
            ['name' => 'View Roles', 'module' => 'Roles'],
            ['name' => 'Create Roles', 'module' => 'Roles'],
            ['name' => 'Edit Roles', 'module' => 'Roles'],
            ['name' => 'Delete Roles', 'module' => 'Roles'],
            ['name' => 'Change Roles Status', 'module' => 'Roles'],

            // Labels
            ['name' => 'View Labels', 'module' => 'Labels'],
            ['name' => 'Create Labels', 'module' => 'Labels'],
            ['name' => 'Edit Labels', 'module' => 'Labels'],
            ['name' => 'Delete Labels', 'module' => 'Labels'],

            // Hotels
            ['name' => 'View Hotels', 'module' => 'Hotels'],
            ['name' => 'Create Hotels', 'module' => 'Hotels'],
            ['name' => 'Edit Hotels', 'module' => 'Hotels'],
            ['name' => 'Delete Hotels', 'module' => 'Hotels'],

            // Vazhipadu
            ['name' => 'View Vazhipadu', 'module' => 'Vazhipadu'],
            ['name' => 'Create Vazhipadu', 'module' => 'Vazhipadu'],
            ['name' => 'Edit Vazhipadu', 'module' => 'Vazhipadu'],
            ['name' => 'Delete Vazhipadu', 'module' => 'Vazhipadu'],
            ['name' => 'Change Vazhipadu Status', 'module' => 'Vazhipadu'],

            // Pooja
            ['name' => 'View Pooja', 'module' => 'Pooja'],
            ['name' => 'Create Pooja', 'module' => 'Pooja'],
            ['name' => 'Edit Pooja', 'module' => 'Pooja'],
            ['name' => 'Delete Pooja', 'module' => 'Pooja'],
            ['name' => 'Change Pooja Status', 'module' => 'Pooja'],

            // News
            ['name' => 'View News', 'module' => 'News'],
            ['name' => 'Create News', 'module' => 'News'],
            ['name' => 'Edit News', 'module' => 'News'],
            ['name' => 'Delete News', 'module' => 'News'],
            ['name' => 'Change News Status', 'module' => 'News'],

            // Festival
            ['name' => 'View Festival', 'module' => 'Festival'],
            ['name' => 'Create Festival', 'module' => 'Festival'],
            ['name' => 'Edit Festival', 'module' => 'Festival'],
            ['name' => 'Delete Festival', 'module' => 'Festival'],
            ['name' => 'Change Festival Status', 'module' => 'Festival'],

            // Event
            ['name' => 'View Event', 'module' => 'Event'],
            ['name' => 'Create Event', 'module' => 'Event'],
            ['name' => 'Edit Event', 'module' => 'Event'],
            ['name' => 'Delete Event', 'module' => 'Event'],
            ['name' => 'Change Event Status', 'module' => 'Event'],
            ['name' => 'Change Event Latest Status', 'module' => 'Event'],

            // Media Types
            ['name' => 'View Media Types', 'module' => 'Media Types'],
            ['name' => 'Create Media Types', 'module' => 'Media Types'],
            ['name' => 'Edit Media Types', 'module' => 'Media Types'],
            ['name' => 'Delete Media Types', 'module' => 'Media Types'],
            ['name' => 'Change Media Types Status', 'module' => 'Media Types'],

            // Medias
            ['name' => 'View Medias', 'module' => 'Medias'],
            ['name' => 'Create Medias', 'module' => 'Medias'],
            ['name' => 'Edit Medias', 'module' => 'Medias'],
            ['name' => 'Delete Medias', 'module' => 'Medias'],
            ['name' => 'Change Medias Status', 'module' => 'Medias'],
        ];

        // Create permissions
        $permissionModel = config('permission.models.permission', \Spatie\Permission\Models\Permission::class);
        foreach ($permissions as $permission) {
            $permissionModel::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['module' => $permission['module']]
            );
        }

        $this->command->info('Permissions seeded successfully!');
    }
}

