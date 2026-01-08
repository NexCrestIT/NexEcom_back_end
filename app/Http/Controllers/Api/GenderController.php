<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\Gender\GenderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenderController extends Controller
{
    protected $genderRepository;

    public function __construct(GenderRepository $genderRepository)
    {
        $this->genderRepository = $genderRepository;
    }

    /**
     * Get all active genders.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $genders = $this->genderRepository->getAllGenders();

            // Filter by status if needed (default to active only)
            $status = $request->get('status', true);
            if ($status !== null) {
                $statusBool = filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($statusBool !== null) {
                    $genders = $genders->filter(function ($gender) use ($statusBool) {
                        return $gender->status === $statusBool;
                    })->values();
                }
            }

            return response()->json([
                'success' => true,
                'data' => $genders,
                'message' => 'Genders retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve genders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single gender by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $gender = $this->genderRepository->getGenderById($id);

            // Check if gender is active (optional - remove if you want to show inactive genders)
            if (!$gender->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gender not found or inactive',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $gender,
                'message' => 'Gender retrieved successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gender not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gender',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

