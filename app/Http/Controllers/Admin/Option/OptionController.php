<?php

namespace App\Http\Controllers\Admin\Option;

use App\Http\Controllers\Controller;
use App\Models\Admin\Option\Option;
use App\Repositories\Admin\Option\OptionRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class OptionController extends Controller
{
    use Toast;

    protected $optionRepository;

    public function __construct(OptionRepository $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'is_active' => $request->get('is_active'),
            'is_required' => $request->get('is_required'),
            'type' => $request->get('type'),
            'search' => $request->get('search'),
        ];

        $options = $this->optionRepository->getAllOptions($filters);

        return Inertia::render('Admin/Option/Index', [
            'options' => $options,
            'statistics' => $this->optionRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Option/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->optionRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Option successfully created');
        return redirect()->route('admin.options.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Option $option)
    {
        return Inertia::render('Admin/Option/Show', [
            'option' => $option,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Option $option)
    {
        return Inertia::render('Admin/Option/Edit', [
            'option' => $option,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Option $option)
    {
        DB::beginTransaction();
        try {
            $this->optionRepository->update($option->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Option successfully updated');
        return redirect()->route('admin.options.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Option $option)
    {
        DB::beginTransaction();
        try {
            $this->optionRepository->delete($option->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Option successfully deleted');
        return redirect()->route('admin.options.index');
    }

    /**
     * Toggle option active status.
     */
    public function toggleStatus(Option $option)
    {
        DB::beginTransaction();
        try {
            $option = $this->optionRepository->toggleStatus($option->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $option->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Option successfully {$status}");
        return back();
    }

    /**
     * Toggle option required status.
     */
    public function toggleRequired(Option $option)
    {
        DB::beginTransaction();
        try {
            $option = $this->optionRepository->toggleRequired($option->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $option->is_required ? 'required' : 'optional';
        $this->toast('success', 'Success', "Option successfully marked as {$status}");
        return back();
    }

    /**
     * Update sort order for options.
     */
    public function updateSortOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sort_data' => 'required|array',
                'sort_data.*' => 'integer|min:0',
            ]);

            $this->optionRepository->updateSortOrder($request->sort_data);
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
     * Bulk delete options.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:options,id',
            ]);

            $this->optionRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'option' : 'options') . " successfully deleted");
        return redirect()->route('admin.options.index');
    }

    /**
     * Get options for dropdown (API endpoint).
     */
    public function dropdown(Request $request)
    {
        $options = $this->optionRepository->getOptionsForDropdown();
        return response()->json([
            'options' => $options,
        ]);
    }
}

