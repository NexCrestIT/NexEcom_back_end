<?php

namespace App\Http\Controllers\Admin\Discount;

use App\Http\Controllers\Controller;
use App\Models\Admin\Discount\Discount;
use App\Repositories\Admin\Discount\DiscountRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class DiscountController extends Controller
{
    use Toast;

    protected $discountRepository;

    public function __construct(DiscountRepository $discountRepository)
    {
        $this->discountRepository = $discountRepository;
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
            'search' => $request->get('search'),
        ];

        $discounts = $this->discountRepository->getAllDiscounts($filters);

        return Inertia::render('Admin/Discount/Index', [
            'discounts' => $discounts,
            'statistics' => $this->discountRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Discount/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->discountRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Discount successfully created');
        return redirect()->route('admin.discounts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        return Inertia::render('Admin/Discount/Show', [
            'discount' => $discount,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discount $discount)
    {
        return Inertia::render('Admin/Discount/Edit', [
            'discount' => $discount,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discount $discount)
    {
        DB::beginTransaction();
        try {
            $this->discountRepository->update($discount->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Discount successfully updated');
        return redirect()->route('admin.discounts.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        DB::beginTransaction();
        try {
            $this->discountRepository->delete($discount->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Discount successfully deleted');
        return redirect()->route('admin.discounts.index');
    }

    /**
     * Toggle discount active status.
     */
    public function toggleStatus(Discount $discount)
    {
        DB::beginTransaction();
        try {
            $discount = $this->discountRepository->toggleStatus($discount->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $discount->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Discount successfully {$status}");
        return back();
    }

    /**
     * Update sort order for discounts.
     */
    public function updateSortOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sort_data' => 'required|array',
                'sort_data.*' => 'integer|min:0',
            ]);

            $this->discountRepository->updateSortOrder($request->sort_data);
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
     * Bulk delete discounts.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:discounts,id',
            ]);

            $this->discountRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'discount' : 'discounts') . " successfully deleted");
        return redirect()->route('admin.discounts.index');
    }

    /**
     * Get discounts for dropdown (API endpoint).
     */
    public function dropdown(Request $request)
    {
        $discounts = $this->discountRepository->getDiscountsForDropdown();
        return response()->json([
            'discounts' => $discounts,
        ]);
    }
}

