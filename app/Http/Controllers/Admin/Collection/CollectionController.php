<?php

namespace App\Http\Controllers\Admin\Collection;

use App\Http\Controllers\Controller;
use App\Models\Admin\Collection\Collection;
use App\Repositories\Admin\Collection\CollectionRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class CollectionController extends Controller
{
    use Toast;

    protected $collectionRepository;

    public function __construct(CollectionRepository $collectionRepository)
    {
        $this->collectionRepository = $collectionRepository;
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

        $collections = $this->collectionRepository->getAllCollections($filters);

        return Inertia::render('Admin/Collection/Index', [
            'collections' => $collections,
            'statistics' => $this->collectionRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Collection/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->collectionRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Collection successfully created');
        return redirect()->route('admin.collections.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Collection $collection)
    {
        return Inertia::render('Admin/Collection/Show', [
            'collection' => $collection,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Collection $collection)
    {
        return Inertia::render('Admin/Collection/Edit', [
            'collection' => $collection,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Collection $collection)
    {
        DB::beginTransaction();
        try {
            $this->collectionRepository->update($collection->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Collection successfully updated');
        return redirect()->route('admin.collections.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Collection $collection)
    {
        DB::beginTransaction();
        try {
            $this->collectionRepository->delete($collection->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Collection successfully deleted');
        return redirect()->route('admin.collections.index');
    }

    /**
     * Toggle collection active status.
     */
    public function toggleStatus(Collection $collection)
    {
        DB::beginTransaction();
        try {
            $collection = $this->collectionRepository->toggleStatus($collection->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $collection->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Collection successfully {$status}");
        return back();
    }

    /**
     * Toggle collection featured status.
     */
    public function toggleFeatured(Collection $collection)
    {
        DB::beginTransaction();
        try {
            $collection = $this->collectionRepository->toggleFeatured($collection->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $collection->is_featured ? 'featured' : 'unfeatured';
        $this->toast('success', 'Success', "Collection successfully {$status}");
        return back();
    }

    /**
     * Update sort order for collections.
     */
    public function updateSortOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sort_data' => 'required|array',
                'sort_data.*' => 'integer|min:0',
            ]);

            $this->collectionRepository->updateSortOrder($request->sort_data);
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
     * Bulk delete collections.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:collections,id',
            ]);

            $this->collectionRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'collection' : 'collections') . " successfully deleted");
        return redirect()->route('admin.collections.index');
    }

    /**
     * Get collections for dropdown (API endpoint).
     */
    public function dropdown(Request $request)
    {
        $collections = $this->collectionRepository->getCollectionsForDropdown();
        return response()->json([
            'collections' => $collections,
        ]);
    }
}

