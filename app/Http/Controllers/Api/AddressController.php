<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Api\AddressRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    protected $addressRepository;

    public function __construct(AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * Get all addresses for the authenticated customer
     */
    public function index()
    {
        $customerId = Auth::id();
        $addresses = $this->addressRepository->getAllAddresses($customerId);
        
        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Get a single address by ID
     */
    public function show($id)
    {
        $customerId = Auth::id();
        
        try {
            $address = $this->addressRepository->getAddressById($customerId, $id);
            
            return response()->json([
                'success' => true,
                'data' => $address,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }
    }

    /**
     * Create a new address
     */
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'landmark' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'identification_mark' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['customer_id'] = Auth::id();

        $address = $this->addressRepository->createAddress($data);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data' => $address,
        ], 201);
    }

    /**
     * Update an existing address
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'address' => 'sometimes|nullable|string|max:255',
            'city' => 'sometimes|nullable|string|max:100',
            'state' => 'sometimes|nullable|string|max:100',
            'district' => 'sometimes|nullable|string|max:100',
            'landmark' => 'sometimes|nullable|string|max:255',
            'pincode' => 'sometimes|nullable|string|max:10',
            'phone' => 'sometimes|nullable|string|max:20',
            'identification_mark' => 'sometimes|nullable|string|max:255',
            'is_default' => 'sometimes|nullable|boolean',
        ]);

        $customerId = Auth::id();

        try {
            $address = $this->addressRepository->updateAddress($customerId, $id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'data' => $address,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }
    }

    /**
     * Delete an address
     */
    public function destroy($id)
    {
        $customerId = Auth::id();

        try {
            $this->addressRepository->deleteAddress($customerId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }
    }
}
