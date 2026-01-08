<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\ScentFamily\ScentFamilyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScentFamilyController extends Controller
{
    protected $scentFamilyRepository;

    public function __construct(ScentFamilyRepository $scentFamilyRepository)
    {
        $this->scentFamilyRepository = $scentFamilyRepository;
    }

    /**
     * Get all active scent families.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $scentFamilies = $this->scentFamilyRepository->getAllScentFamilies();

            // Filter by status if needed (default to active only)
            $status = $request->get('status', true);
            if ($status !== null) {
                $statusBool = filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($statusBool !== null) {
                    $scentFamilies = $scentFamilies->filter(function ($scentFamily) use ($statusBool) {
                        return $scentFamily->status === $statusBool;
                    })->values();
                }
            }

            return response()->json([
                'success' => true,
                'data' => $scentFamilies,
                'message' => 'Scent families retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve scent families',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single scent family by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $scentFamily = $this->scentFamilyRepository->getScentFamilyById($id);

            // Check if scent family is active (optional - remove if you want to show inactive ones)
            if (!$scentFamily->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scent family not found or inactive',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $scentFamily,
                'message' => 'Scent family retrieved successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Scent family not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve scent family',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

