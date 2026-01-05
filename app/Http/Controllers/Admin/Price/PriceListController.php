<?php

namespace App\Http\Controllers\Admin\Price;

use App\Http\Controllers\Controller;
use App\Models\Admin\Price\PriceList;
use App\Repositories\Admin\Price\PriceListRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class PriceListController extends Controller
{
    use Toast;

    protected $priceListRepository;

    public function __construct(PriceListRepository $priceListRepository)
    {
        $this->priceListRepository = $priceListRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'is_active' => $request->get('is_active'),
            'type' => $request->get('type'),
            'is_default' => $request->get('is_default'),
            'search' => $request->get('search'),
        ];

        $priceLists = $this->priceListRepository->getAllPriceLists($filters);

        return Inertia::render('Admin/Price/Index', [
            'priceLists' => $priceLists,
            'statistics' => $this->priceListRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categoryRepository = app(\App\Repositories\Admin\Category\CategoryRepository::class);
        $productRepository = app(\App\Repositories\Admin\Product\ProductRepository::class);

        return Inertia::render('Admin/Price/Create', [
            'categories' => $categoryRepository->getCategoriesForDropdown(),
            'products' => $productRepository->getAllProducts()->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
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
            $this->priceListRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Price list successfully created');
        return redirect()->route('admin.price-lists.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceList $priceList)
    {
        $priceList->load(['priceRules.product', 'priceHistory.changedBy']);
        
        return Inertia::render('Admin/Price/Show', [
            'priceList' => $priceList,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PriceList $priceList)
    {
        $categoryRepository = app(\App\Repositories\Admin\Category\CategoryRepository::class);
        $productRepository = app(\App\Repositories\Admin\Product\ProductRepository::class);

        return Inertia::render('Admin/Price/Edit', [
            'priceList' => $priceList,
            'categories' => $categoryRepository->getCategoriesForDropdown(),
            'products' => $productRepository->getAllProducts()->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ]),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PriceList $priceList)
    {
        DB::beginTransaction();
        try {
            $this->priceListRepository->update($priceList->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Price list successfully updated');
        return redirect()->route('admin.price-lists.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceList $priceList)
    {
        DB::beginTransaction();
        try {
            $this->priceListRepository->delete($priceList->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Price list successfully deleted');
        return redirect()->route('admin.price-lists.index');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(PriceList $priceList)
    {
        try {
            $this->priceListRepository->toggleStatus($priceList->id);
            $this->toast('success', 'Success', 'Price list status updated');
        } catch (Throwable $th) {
            $this->toast('error', 'Error', $th->getMessage());
        }
        return back();
    }

    /**
     * Set as default price list.
     */
    public function setAsDefault(PriceList $priceList)
    {
        try {
            $this->priceListRepository->setAsDefault($priceList->id);
            $this->toast('success', 'Success', 'Price list set as default');
        } catch (Throwable $th) {
            $this->toast('error', 'Error', $th->getMessage());
        }
        return back();
    }

    /**
     * Update sort order.
     */
    public function updateSortOrder(Request $request)
    {
        try {
            $request->validate([
                'sort_order' => 'required|array',
                'sort_order.*' => 'integer',
            ]);

            $this->priceListRepository->updateSortOrder($request->sort_order);
            $this->toast('success', 'Success', 'Sort order updated');
        } catch (Throwable $th) {
            $this->toast('error', 'Error', $th->getMessage());
        }
        return back();
    }

    /**
     * Bulk delete price lists.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:price_lists,id',
            ]);

            $this->priceListRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'price list' : 'price lists') . " successfully deleted");
        return redirect()->route('admin.price-lists.index');
    }

    /**
     * Get price lists for dropdown.
     */
    public function dropdown()
    {
        return response()->json($this->priceListRepository->getPriceListsForDropdown());
    }
}
