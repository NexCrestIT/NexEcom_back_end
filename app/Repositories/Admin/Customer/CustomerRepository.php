<?php

namespace App\Repositories\Admin\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerRepository
{
    /**
     * Get all customers with filters.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCustomers(array $filters = [])
    {
        $query = Customer::query();

        // Apply status filter
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $statusBool = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($statusBool !== null) {
                $query->where('is_active', $statusBool);
            }
        }

        // Apply verified filter
        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $verifiedBool = filter_var($filters['is_verified'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($verifiedBool !== null) {
                $query->where('is_verified', $verifiedBool);
            }
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get paginated customers.
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedCustomers($perPage = 15, array $filters = [])
    {
        $query = Customer::query();

        // Apply status filter
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $statusBool = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($statusBool !== null) {
                $query->where('is_active', $statusBool);
            }
        }

        // Apply verified filter
        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $verifiedBool = filter_var($filters['is_verified'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($verifiedBool !== null) {
                $query->where('is_verified', $verifiedBool);
            }
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get customer by ID.
     *
     * @param int $id
     * @return Customer
     */
    public function getCustomerById($id)
    {
        return Customer::findOrFail($id);
    }

    /**
     * Update customer.
     *
     * @param int $id
     * @param array $data
     * @return Customer
     */
    public function update($id, $data)
    {
        $customer = Customer::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'] ?? $customer->name,
            'email' => $data['email'] ?? $customer->email,
            'phone' => $data['phone'] ?? $customer->phone,
            'date_of_birth' => $data['date_of_birth'] ?? $customer->date_of_birth,
            'gender' => $data['gender'] ?? $customer->gender,
            'is_active' => $data['is_active'] ?? $customer->is_active,
            'is_verified' => $data['is_verified'] ?? $customer->is_verified,
        ];

        // Update password only if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $customer->update($updateData);
        return $customer->fresh();
    }

    /**
     * Delete customer.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        return $customer->delete();
    }

    /**
     * Toggle customer active status.
     *
     * @param int $id
     * @return Customer
     */
    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['is_active' => !$customer->is_active]);
        return $customer->fresh();
    }

    /**
     * Bulk delete customers.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids)
    {
        $customers = Customer::whereIn('id', $ids)->get();
        foreach ($customers as $customer) {
            $customer->delete();
        }
        return true;
    }

    /**
     * Get statistics.
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total' => Customer::count(),
            'active' => Customer::where('is_active', true)->count(),
            'inactive' => Customer::where('is_active', false)->count(),
            'verified' => Customer::where('is_verified', true)->count(),
            'unverified' => Customer::where('is_verified', false)->count(),
        ];
    }
}

