<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\Category\CategoryRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class CategoryController extends Controller
{
    use Toast;

    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $viewType = $request->get('view', 'list'); // list or tree

        // Get filter parameters
        $filters = [
            'status' => $request->get('status'),
            'featured' => $request->get('featured'),
            'parent_id' => $request->get('parent_id'),
            'search' => $request->get('search'),
        ];

        if ($viewType === 'tree') {
            $categories = $this->categoryRepository->getCategoryTree($filters);
        } else {
            $categories = $this->categoryRepository->getAllCategories($filters);
        }

        // Get root categories for parent filter dropdown
        $rootCategories = $this->categoryRepository->getRootCategories();

        return Inertia::render('Admin/Category/Index', [
            'categories' => $categories,
            'viewType' => $viewType,
            'statistics' => $this->categoryRepository->getStatistics(),
            'filters' => $filters,
            'rootCategories' => $rootCategories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = $this->categoryRepository->getCategoriesForDropdown();

        return Inertia::render('Admin/Category/Create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->categoryRepository->store($request->all());     
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Category successfully created');
        return redirect()->route('admin.categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $category)
    {
        $category = $this->categoryRepository->getCategoryById($category);
        $statistics = [
            'children_count' => $category->children->count(),
            'descendants_count' => count($category->getDescendantIds()),
        ];

        return Inertia::render('Admin/Category/Show', [
            'category' => $category,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $category)
    {
        $cat = $this->categoryRepository->getCategoryById($category);
        $parentCategories = $this->categoryRepository->getCategoriesForDropdown($category);

        return Inertia::render('Admin/Category/Edit', [
            'category' => $cat,
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $category)
    {
        DB::beginTransaction();
        try {
            $this->categoryRepository->update($category, $request->all());
         
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Category successfully updated');
        return redirect()->route('admin.categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $category)
    {
        DB::beginTransaction();
        try {
            $this->categoryRepository->delete($category);
         
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Category successfully deleted');
        return redirect()->route('admin.categories.index');
    }

    /**
     * Toggle category active status.
     */
    public function toggleStatus(string $id)
    {
        DB::beginTransaction();
        try {
            $category = $this->categoryRepository->toggleStatus($id);
         
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $category->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Category successfully {$status}");
        return back();
    }

    /**
     * Toggle category featured status.
     */
    public function toggleFeatured(string $id)
    {
        DB::beginTransaction();
        try {
            $category = $this->categoryRepository->toggleFeatured($id);
         
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $category->is_featured ? 'featured' : 'unfeatured';
        $this->toast('success', 'Success', "Category successfully marked as {$status}");
        return back();
    }

    /**
     * Update sort order for categories.
     */
    public function updateSortOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sort_data' => 'required|array',
                'sort_data.*' => 'integer|min:0',
            ]);

            $this->categoryRepository->updateSortOrder($request->sort_data);
         
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
     * Move category to a new parent.
     */
    public function moveCategory(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'parent_id' => 'nullable|integer|exists:categories,id',
            ]);

            $this->categoryRepository->moveCategory($id, $request->parent_id);
         
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Category moved successfully');
        return back();
    }

    /**
     * Restore a soft-deleted category.
     */
    public function restore(string $id)
    {
        DB::beginTransaction();
        try {
            $this->categoryRepository->restore($id);
         
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Category restored successfully');
        return back();
    }

    /**
     * Search categories.
     */
    public function search(Request $request)
    {
        $term = $request->get('q', '');
        $categories = $this->categoryRepository->searchCategories($term);

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Get categories for dropdown (API endpoint).
     */
    public function dropdown(Request $request)
    {
        $excludeId = $request->get('exclude');
        $categories = $this->categoryRepository->getCategoriesForDropdown($excludeId);

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:categories,id',
            ]);

            $this->categoryRepository->bulkDelete($request->ids);
         
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'category' : 'categories') . " successfully deleted");
        return redirect()->route('admin.categories.index');
    }
}

