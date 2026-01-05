<?php

namespace App\Repositories\Admin\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    /**
     * Get all users with roles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUsers()
    {
        return User::with('roles')->get();
    }

    /**
     * Get paginated users
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedUsers($perPage = 15)
    {
        return User::with('roles')->paginate($perPage);
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById($id)
    {
        return User::with('roles')->findOrFail($id);
    }

    /**
     * Store a new user
     *
     * @param array $data
     * @return User
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        // Assign roles if provided
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user;
    }

    /**
     * Update user
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function update($id, $data)
    {
        $this->validateData($data, true);
        
        $user = User::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        // Update password only if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        // Sync roles if provided
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user->fresh(['roles']);
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }

    /**
     * Update user status
     *
     * @param int $id
     * @param string $status
     * @return User
     */
    public function updateStatus($id, $status)
    {
        $user = User::findOrFail($id);
        // Assuming you have a status field, adjust as needed
        // $user->update(['status' => $status]);
        return $user;
    }

    /**
     * Validate user data
     *
     * @param array $data
     * @param bool $isUpdate
     * @return array
     */
    public function validateData($data, $isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];

        if ($isUpdate) {
            $userId = $data['id'] ?? null;
            if ($userId) {
                $rules['email'] = 'required|string|email|max:255|unique:users,email,' . $userId;
            } else {
                $rules['email'] = 'required|string|email|max:255|unique:users,email';
            }
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            $rules['email'] = 'required|string|email|max:255|unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return validator($data, $rules)->validate();
    }
}
