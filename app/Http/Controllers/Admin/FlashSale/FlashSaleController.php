<?php

namespace App\Http\Controllers\Admin\FlashSale;

use App\Http\Controllers\Controller;
use App\Models\Admin\FlashSale\FlashSale;
use App\Repositories\Admin\FlashSale\FlashSaleRepository;
use App\Repositories\Admin\Product\ProductRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class FlashSaleController extends Controller
{
    use Toast;

    protected $flashSaleRepository;
    protected $productRepository;

    public function __construct(FlashSaleRepository $flashSaleRepository, ProductRepository $productRepository)
    {
        $this->flashSaleRepository = $flashSaleRepository;
        $this->productRepository = $productRepository;
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
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $flashSales = $this->flashSaleRepository->getAllFlashSales($filters);

        return Inertia::render('Admin/FlashSale/Index', [
            'flashSales' => $flashSales,
            'statistics' => $this->flashSaleRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/FlashSale/Create', [
            'products' => $this->productRepository->getAllProducts()->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->flashSaleRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Flash sale successfully created');
        return redirect()->route('admin.flash-sales.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(FlashSale $flashSale)
    {
        $flashSale->load('products');
        return Inertia::render('Admin/FlashSale/Show', [
            'flashSale' => $flashSale,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FlashSale $flashSale)
    {
        $flashSale->load('products');
        return Inertia::render('Admin/FlashSale/Edit', [
            'flashSale' => $flashSale,
            'products' => $this->productRepository->getAllProducts()->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FlashSale $flashSale)
    {
        DB::beginTransaction();
        try {
            $this->flashSaleRepository->update($flashSale->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Flash sale successfully updated');
        return redirect()->route('admin.flash-sales.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FlashSale $flashSale)
    {
        DB::beginTransaction();
        try {
            $this->flashSaleRepository->delete($flashSale->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Flash sale successfully deleted');
        return redirect()->route('admin.flash-sales.index');
    }

    /**
     * Toggle flash sale active status.
     */
    public function toggleStatus(FlashSale $flashSale)
    {
        DB::beginTransaction();
        try {
            $flashSale = $this->flashSaleRepository->toggleStatus($flashSale->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $flashSale->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Flash sale successfully {$status}");
        return back();
    }

    /**
     * Toggle flash sale featured status.
     */
    public function toggleFeatured(FlashSale $flashSale)
    {
        DB::beginTransaction();
        try {
            $flashSale = $this->flashSaleRepository->toggleFeatured($flashSale->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $flashSale->is_featured ? 'featured' : 'unfeatured';
        $this->toast('success', 'Success', "Flash sale successfully {$status}");
        return back();
    }

    /**
     * Update sort order for flash sales.
     */
    public function updateSortOrder(Request $request, FlashSale $flashSale)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sort_order' => 'required|integer|min:0',
            ]);

            $this->flashSaleRepository->updateSortOrder($flashSale->id, $request->sort_order);
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
     * Bulk delete flash sales.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:flash_sales,id',
            ]);

            $this->flashSaleRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'flash sale' : 'flash sales') . " successfully deleted");
        return redirect()->route('admin.flash-sales.index');
    }

    /**
     * Get flash sales for dropdown (API endpoint).
     */
    public function dropdown(Request $request)
    {
        $flashSales = $this->flashSaleRepository->getFlashSalesForDropdown();
        return response()->json([
            'flashSales' => $flashSales,
        ]);
    }
}
