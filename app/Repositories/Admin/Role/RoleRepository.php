<?php

namespace App\Repositories\Admin\Role;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleRepository
{
    /**
     * Get all roles with permissions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRoles()
    {
        return Role::with('permissions')->get();
    }

    /**
     * Get paginated roles
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedRoles($perPage = 15)
    {
        return Role::with('permissions')->paginate($perPage);
    }

    /**
     * Get role by ID
     *
     * @param int $id
     * @return Role|null
     */
    public function getRoleById($id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    /**
     * Store a new role
     *
     * @param array $data
     * @return Role
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        // Assign permissions if provided
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    /**
     * Update role
     *
     * @param int $id
     * @param array $data
     * @return Role
     */
    public function update($id, $data)
    {
        $this->validateData($data, true);
        
        $role = Role::findOrFail($id);
        
        $role->update([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        // Sync permissions if provided
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->fresh(['permissions']);
    }

    /**
     * Delete role
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $role = Role::findOrFail($id);
        return $role->delete();
    }

    /**
     * Get all permissions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions()
    {
        return Permission::where('guard_name', 'web')->get();
    }

    /**
     * Get permissions grouped by module
     *
     * @return array
     */
    public function getPermissionsByModule()
    {
        return Permission::where('guard_name', 'web')
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
    }

    /**
     * Validate role data
     *
     * @param array $data
     * @param bool $isUpdate
     * @return array
     */
    public function validateData($data, $isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];

        if ($isUpdate) {
            $rules['name'] = 'required|string|max:255|unique:roles,name,' . ($data['id'] ?? '');
        }

        return validator($data, $rules)->validate();
    }
}

