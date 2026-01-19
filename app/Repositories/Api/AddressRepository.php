<?php

namespace App\Repositories\Api;

use App\Models\Address;

class AddressRepository
{
    /**
     * Get all addresses for a customer
     */
    public function getAllAddresses($customerId)
    {
        return Address::where('customer_id', $customerId)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a single address by ID
     */
    public function getAddressById($customerId, $addressId)
    {
        return Address::where('customer_id', $customerId)
            ->where('id', $addressId)
            ->firstOrFail();
    }

    /**
     * Create a new address
     */
    public function createAddress(array $data)
    {
        // If this address is set as default, unset all other defaults for this customer
        if (!empty($data['is_default'])) {
            Address::where('customer_id', $data['customer_id'])
                ->update(['is_default' => false]);
        }

        return Address::create($data);
    }

    /**
     * Update an existing address
     */
    public function updateAddress($customerId, $addressId, array $data)
    {
        $address = $this->getAddressById($customerId, $addressId);

        // If this address is set as default, unset all other defaults for this customer
        if (!empty($data['is_default'])) {
            Address::where('customer_id', $customerId)
                ->where('id', '!=', $addressId)
                ->update(['is_default' => false]);
        }

        $address->update($data);
        return $address->fresh();
    }

    /**
     * Delete an address
     */
    public function deleteAddress($customerId, $addressId)
    {
        $address = $this->getAddressById($customerId, $addressId);
        return $address->delete();
    }
}
