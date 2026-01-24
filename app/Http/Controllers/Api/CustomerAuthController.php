<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerAuthController extends Controller
{
    /**
     * Register a new customer.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->whereNull('deleted_at'),
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[+]?[0-9]{10,15}$/',
                Rule::unique('customers', 'phone_number')->whereNull('deleted_at'),
            ],
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
        ], [
            'email.unique' => 'This email is already registered.',
            'phone_number.unique' => 'This phone number is already registered.',
            'phone_number.regex' => 'Please enter a valid phone number.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Ensure at least email or phone_number is provided
        $validator->after(function ($validator) use ($request) {
            if (empty($request->email) && empty($request->phone_number)) {
                $validator->errors()->add('email', 'Either email or phone number is required.');
                $validator->errors()->add('phone_number', 'Either email or phone number is required.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $customer = Customer::create([
                'name' => $request->name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'city' => $request->city,
                'state' => $request->state,
                'postcode' => $request->postcode,
                'country' => $request->country,
                'password' => Hash::make($request->password),
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'is_active' => true,
                'is_verified' => false,
            ]);

            if ($request->email) {
                $customer->email_verified_at = now();
            }
            if ($request->phone_number) {
                $customer->phone_verified_at = now();
            }
            $customer->save();

            $token = $customer->createToken('customer-api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Customer registered successfully',
                'data' => [
                    'customer' => $customer->makeHidden(['password', 'verification_code']),
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login customer with email or phone.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // Can be email or phone
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find customer by email or phone
            $customer = Customer::findByEmailOrPhone($request->identifier);

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            // Check if customer is active
            if (!$customer->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact support.',
                ], 403);
            }

            // Verify password
            if (!Hash::check($request->password, $customer->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            // Update last login info
            $customer->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Generate Sanctum token for API authentication
            $token = $customer->createToken('customer-api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'customer' => $customer->makeHidden(['password', 'verification_code']),
                    'token' => $token,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout customer.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $customer = $request->user();

            if ($customer) {
                // Revoke the current token (Sanctum)
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated customer.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $customer = $request->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $customer->makeHidden(['password', 'verification_code']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update authenticated customer profile.
     */
    public function update(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('customers', 'email')
                    ->ignore($customer->id)
                    ->whereNull('deleted_at'),
            ],
            'phone_number' => [
                'nullable',
                'regex:/^[+]?[0-9]{10,15}$/',
                Rule::unique('customers', 'phone_number')
                    ->ignore($customer->id)
                    ->whereNull('deleted_at'),
            ],
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $customer->fresh()->makeHidden(['password', 'verification_code']),
        ], 200);
    }
}
