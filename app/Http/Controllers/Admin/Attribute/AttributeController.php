<?php

namespace App\Http\Controllers\Admin\Attribute;

use App\Http\Controllers\Controller;
use App\Models\Admin\Attribute\Attribute;
use App\Repositories\Admin\Attribute\AttributeRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class AttributeController extends Controller
{
    use Toast;

    protected $attributeRepository;

    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'type' => $request->get('type'),
            'is_active' => $request->get('is_active'),
            'is_filterable' => $request->get('is_filterable'),
            'search' => $request->get('search'),
        ];

        $attributes = $this->attributeRepository->getAllAttributes($filters);

        return Inertia::render('Admin/Attribute/Index', [
            'attributes' => $attributes,
            'statistics' => $this->attributeRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Attribute/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->attributeRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Attribute successfully created');
        return redirect()->route('admin.attributes.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        $attribute->load('values');
        return Inertia::render('Admin/Attribute/Show', [
            'attribute' => $attribute,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attribute $attribute)
    {
        $attribute->load('values');
        return Inertia::render('Admin/Attribute/Edit', [
            'attribute' => $attribute,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attribute $attribute)
    {
        DB::beginTransaction();
        try {
            $this->attributeRepository->update($attribute->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Attribute successfully updated');
        return redirect()->route('admin.attributes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        DB::beginTransaction();
        try {
            $this->attributeRepository->delete($attribute->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Attribute successfully deleted');
        return redirect()->route('admin.attributes.index');
    }

    /**
     * Toggle attribute active status.
     */
    public function toggleStatus(Attribute $attribute)
    {
        DB::beginTransaction();
        try {
            $attribute = $this->attributeRepository->toggleStatus($attribute->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $attribute->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Attribute successfully {$status}");
        return back();
    }

    /**
     * Toggle attribute filterable status.
     */
    public function toggleFilterable(Attribute $attribute)
    {
        DB::beginTransaction();
        try {
            $attribute = $this->attributeRepository->toggleFilterable($attribute->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $attribute->is_filterable ? 'enabled' : 'disabled';
        $this->toast('success', 'Success', "Attribute filterable status {$status}");
        return back();
    }

    /**
     * Update sort order for attributes.
     */
    public function updateSortOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sort_data' => 'required|array',
                'sort_data.*' => 'integer|min:0',
            ]);

            $this->attributeRepository->updateSortOrder($request->sort_data);
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
     * Bulk delete attributes.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:attributes,id',
            ]);

            $this->attributeRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'attribute' : 'attributes') . " successfully deleted");
        return redirect()->route('admin.attributes.index');
    }

    /**
     * Get attributes for dropdown (API endpoint).
     */
    public function dropdown(Request $request)
    {
        $attributes = $this->attributeRepository->getAttributesForDropdown();
        return response()->json([
            'attributes' => $attributes,
        ]);
    }
}

