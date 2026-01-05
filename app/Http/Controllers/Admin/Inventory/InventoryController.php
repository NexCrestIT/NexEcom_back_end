<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Admin\Inventory\Inventory;
use App\Repositories\Admin\Inventory\InventoryRepository;
use App\Repositories\Admin\Product\ProductRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class InventoryController extends Controller
{
    use Toast;

    protected $inventoryRepository;
    protected $productRepository;

    public function __construct(InventoryRepository $inventoryRepository, ProductRepository $productRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'product_id' => $request->get('product_id'),
            'location' => $request->get('location'),
            'stock_status' => $request->get('stock_status'),
            'search' => $request->get('search'),
        ];

        $inventory = $this->inventoryRepository->getAllInventory($filters);

        return Inertia::render('Admin/Inventory/Index', [
            'inventory' => $inventory,
            'statistics' => $this->inventoryRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Inventory/Create', [
            'products' => $this->productRepository->getAllProducts()->map(fn($product) => [
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
            $inventory = $this->inventoryRepository->store($request->all());
            $this->inventoryRepository->syncProductStock($inventory->product_id);
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Inventory successfully created');
        return redirect()->route('admin.inventory.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        $inventory->load(['product', 'stockMovements' => function($query) {
            $query->with('user')->orderBy('created_at', 'desc')->limit(100);
        }]);
        
        return Inertia::render('Admin/Inventory/Show', [
            'inventory' => $inventory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory $inventory)
    {
        $inventory->load('product');
        return Inertia::render('Admin/Inventory/Edit', [
            'inventory' => $inventory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $inventory)
    {
        DB::beginTransaction();
        try {
            $this->inventoryRepository->update($inventory->id, $request->all());
            $this->inventoryRepository->syncProductStock($inventory->product_id);
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Inventory successfully updated');
        return redirect()->route('admin.inventory.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        DB::beginTransaction();
        try {
            $productId = $inventory->product_id;
            $inventory->delete();
            $this->inventoryRepository->syncProductStock($productId);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Inventory successfully deleted');
        return redirect()->route('admin.inventory.index');
    }

    /**
     * Adjust stock quantity.
     */
    public function adjustStock(Request $request, Inventory $inventory)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'quantity' => 'required|integer',
                'type' => 'required|in:in,out,adjustment',
                'reason' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            $this->inventoryRepository->adjustStock(
                $inventory->id,
                $request->quantity,
                $request->type,
                [
                    'reason' => $request->reason,
                    'notes' => $request->notes,
                ]
            );
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Stock successfully adjusted');
        return back();
    }

    /**
     * Get stock movements.
     */
    public function stockMovements(Request $request)
    {
        $filters = [
            'product_id' => $request->get('product_id'),
            'type' => $request->get('type'),
            'location' => $request->get('location'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $movements = $this->inventoryRepository->getStockMovements($filters);

        return Inertia::render('Admin/Inventory/StockMovements', [
            'movements' => $movements,
            'filters' => $filters,
        ]);
    }

    /**
     * Get low stock items.
     */
    public function lowStock()
    {
        $lowStockItems = $this->inventoryRepository->getLowStockItems();

        return Inertia::render('Admin/Inventory/LowStock', [
            'lowStockItems' => $lowStockItems,
        ]);
    }

    /**
     * Bulk delete inventory.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:inventory,id',
            ]);

            $inventoryItems = Inventory::whereIn('id', $request->ids)->get();
            $productIds = $inventoryItems->pluck('product_id')->unique();

            Inventory::whereIn('id', $request->ids)->delete();

            // Sync product stock for affected products
            foreach ($productIds as $productId) {
                $this->inventoryRepository->syncProductStock($productId);
            }
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'inventory item' : 'inventory items') . " successfully deleted");
        return redirect()->route('admin.inventory.index');
    }
}
