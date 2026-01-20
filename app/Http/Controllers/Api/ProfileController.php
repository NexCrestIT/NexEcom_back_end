<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Api\ProfileRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $profileRepository;

    public function __construct(ProfileRepository $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    /**
     * Get all profiles (paginated)
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $profiles = $this->profileRepository->getAll(15);
            return response()->json([
                'success' => true,
                'message' => 'Profiles retrieved successfully',
                'data' => $profiles,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profiles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get profile by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $profile = $this->profileRepository->getById($id);
            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $profile,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current customer profile
     *
     * @return JsonResponse
     */
    public function myProfile(): JsonResponse
    {
        try {
            $customerId = auth('sanctum')->id();
            $profile = $this->profileRepository->getByCustomerId($customerId);

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $profile,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->except('profile_image');
            $profileImage = $request->file('profile_image');
            $profile = $this->profileRepository->create($data, $profileImage);

            return response()->json([
                'success' => true,
                'message' => 'Profile created successfully',
                'data' => $profile,
            ], 201);
        } catch (Exception $e) {
            $errors = json_decode($e->getMessage(), true);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors ?? $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update a profile
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data = $request->except('profile_image');
            $profileImage = $request->file('profile_image');
            $profile = $this->profileRepository->update($id, $data, $profileImage);

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $profile,
            ], 200);
        } catch (Exception $e) {
            $errors = json_decode($e->getMessage(), true);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors ?? $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update current customer profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateMyProfile(Request $request): JsonResponse
    {
        try {
            $customerId = auth('sanctum')->id();
            $data = $request->except('profile_image');
            $profileImage = $request->file('profile_image');
            $profile = $this->profileRepository->updateByCustomerId($customerId, $data, $profileImage);

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $profile,
            ], 200);
        } catch (Exception $e) {
            $errors = json_decode($e->getMessage(), true);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors ?? $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a profile
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->profileRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
