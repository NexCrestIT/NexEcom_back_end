<?php

namespace App\Http\Controllers\Admin\Brand;

use App\Http\Controllers\Controller;
use App\Models\Admin\Brand\Brand;
use App\Repositories\Admin\Brand\BrandRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class BrandController extends Controller
{
    use Toast;

    protected $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'is_active' => $request->get('is_active'),
            'is_featured' => $request->get('is_featured'),
            'search' => $request->get('search'),
        ];

        $brands = $this->brandRepository->getAllBrands($filters);

        return Inertia::render('Admin/Brand/Index', [
            'brands' => $brands,
            'statistics' => $this->brandRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Brand/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->brandRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Brand successfully created');
        return redirect()->route('admin.brands.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return Inertia::render('Admin/Brand/Show', [
            'brand' => $brand,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        return Inertia::render('Admin/Brand/Edit', [
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        DB::beginTransaction();
        try {
            $this->brandRepository->update($brand->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Brand successfully updated');
        return redirect()->route('admin.brands.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        DB::beginTransaction();
        try {
            $this->brandRepository->delete($brand->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Brand successfully deleted');
        return redirect()->route('admin.brands.index');
    }

    /**
     * Toggle brand active status.
     */
    public function toggleStatus(Brand $brand)
    {
        DB::beginTransaction();
        try {
            $brand = $this->brandRepository->toggleStatus($brand->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $brand->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Brand successfully {$status}");
        return back();
    }

    /**
     * Toggle brand featured status.
     */
    public function toggleFeatured(Brand $brand)
    {
        DB::beginTransaction();
        try {
            $brand = $this->brandRepository->toggleFeatured($brand->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $brand->is_featured ? 'featured' : 'unfeatured';
        $this->toast('success', 'Success', "Brand successfully {$status}");
        return back();
    }

    /**
     * Update sort order for brands.
     */
    public function updateSortOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sort_data' => 'required|array',
                'sort_data.*' => 'integer|min:0',
            ]);

            $this->brandRepository->updateSortOrder($request->sort_data);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Sort order updated successfully');
        return back();
    }

    /**
     * Bulk delete brands.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:brands,id',
            ]);

            $this->brandRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'brand' : 'brands') . " successfully deleted");
        return redirect()->route('admin.brands.index');
    }

    /**
     * Get brands for dropdown (API endpoint).
     */
    public function dropdown(Request $request)
    {
        $brands = $this->brandRepository->getBrandsForDropdown();
        return response()->json([
            'brands' => $brands,
        ]);
    }
}

