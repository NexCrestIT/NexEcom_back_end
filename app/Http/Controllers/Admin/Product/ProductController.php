<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\Admin\Product\Product;
use App\Repositories\Admin\Product\ProductRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class ProductController extends Controller
{
    use Toast;

    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
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
            'category_id' => $request->get('category_id'),
            'brand_id' => $request->get('brand_id'),
            'collection_id' => $request->get('collection_id'),
            'stock_status' => $request->get('stock_status'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'search' => $request->get('search'),
        ];

        $products = $this->productRepository->getAllProducts($filters);

        return Inertia::render('Admin/Product/Index', [
            'products' => $products,
            'statistics' => $this->productRepository->getStatistics(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categoryRepository = app(\App\Repositories\Admin\Category\CategoryRepository::class);
        $brandRepository = app(\App\Repositories\Admin\Brand\BrandRepository::class);
        $collectionRepository = app(\App\Repositories\Admin\Collection\CollectionRepository::class);
        $genderRepository = app(\App\Repositories\Admin\Gender\GenderRepository::class);
        $tagRepository = app(\App\Repositories\Admin\Tag\TagRepository::class);
        $labelRepository = app(\App\Repositories\Admin\Label\LabelRepository::class);
        $discountRepository = app(\App\Repositories\Admin\Discount\DiscountRepository::class);
        $attributeRepository = app(\App\Repositories\Admin\Attribute\AttributeRepository::class);

        return Inertia::render('Admin/Product/Create', [
            'categories' => $categoryRepository->getCategoriesForDropdown(),
            'brands' => $brandRepository->getBrandsForDropdown(),
            'collections' => $collectionRepository->getCollectionsForDropdown(),
            'genders' => $genderRepository->getGendersForDropdown(),
            'tags' => $tagRepository->getTagsForDropdown(),
            'labels' => $labelRepository->getAllLabels()->map(fn($label) => ['id' => $label->id, 'name' => $label->name]),
            'discounts' => $discountRepository->getDiscountsForDropdown(),
            'attributes' => $attributeRepository->getAllAttributes()->load('values')->map(function($attr) {
                return [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'type' => $attr->type,
                    'values' => $attr->values->map(fn($val) => ['id' => $val->id, 'value' => $val->value, 'display_value' => $val->display_value]),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->productRepository->store($request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Product successfully created');
        return redirect()->route('admin.products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'category', 
            'brand', 
            'collection', 
            'tags', 
            'labels', 
            'attributes.values', 
            'discounts'
        ]);
        return Inertia::render('Admin/Product/Show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load([
            'category', 
            'brand', 
            'collection', 
            'gender',
            'tags', 
            'labels', 
            'attributes.values', 
            'discounts'
        ]);
        
        $categoryRepository = app(\App\Repositories\Admin\Category\CategoryRepository::class);
        $brandRepository = app(\App\Repositories\Admin\Brand\BrandRepository::class);
        $collectionRepository = app(\App\Repositories\Admin\Collection\CollectionRepository::class);
        $genderRepository = app(\App\Repositories\Admin\Gender\GenderRepository::class);
        $tagRepository = app(\App\Repositories\Admin\Tag\TagRepository::class);
        $labelRepository = app(\App\Repositories\Admin\Label\LabelRepository::class);
        $discountRepository = app(\App\Repositories\Admin\Discount\DiscountRepository::class);
        $attributeRepository = app(\App\Repositories\Admin\Attribute\AttributeRepository::class);

        return Inertia::render('Admin/Product/Edit', [
            'product' => $product,
            'categories' => $categoryRepository->getCategoriesForDropdown(),
            'brands' => $brandRepository->getBrandsForDropdown(),
            'collections' => $collectionRepository->getCollectionsForDropdown(),
            'genders' => $genderRepository->getGendersForDropdown(),
            'tags' => $tagRepository->getTagsForDropdown(),
            'labels' => $labelRepository->getAllLabels()->map(fn($label) => ['id' => $label->id, 'name' => $label->name]),
            'discounts' => $discountRepository->getDiscountsForDropdown(),
            'attributes' => $attributeRepository->getAllAttributes()->load('values')->map(function($attr) {
                return [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'type' => $attr->type,
                    'values' => $attr->values->map(fn($val) => ['id' => $val->id, 'value' => $val->value, 'display_value' => $val->display_value]),
                ];
            }),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $this->productRepository->update($product->id, $request->all());
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Product successfully updated');
        return redirect()->route('admin.products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $this->productRepository->delete($product->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Product successfully deleted');
        return redirect()->route('admin.products.index');
    }

    /**
     * Toggle product active status.
     */
    public function toggleStatus(Product $product)
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->toggleStatus($product->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $product->is_active ? 'activated' : 'deactivated';
        $this->toast('success', 'Success', "Product successfully {$status}");
        return back();
    }

    /**
     * Toggle product featured status.
     */
    public function toggleFeatured(Product $product)
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->toggleFeatured($product->id);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $status = $product->is_featured ? 'featured' : 'unfeatured';
        $this->toast('success', 'Success', "Product successfully {$status}");
        return back();
    }

    /**
     * Update stock quantity.
     */
    public function updateStock(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'quantity' => 'required|integer|min:0',
            ]);

            $product = $this->productRepository->updateStock($product->id, $request->quantity);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $this->toast('success', 'Success', 'Stock quantity updated successfully');
        return back();
    }

    /**
     * Update sort order for products.
     */
    public function updateSortOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sort_data' => 'required|array',
                'sort_data.*' => 'integer|min:0',
            ]);

            $this->productRepository->updateSortOrder($request->sort_data);
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
     * Bulk delete products.
     */
    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:products,id',
            ]);

            $this->productRepository->bulkDelete($request->ids);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', 'Error', $th->getMessage());
            return back();
        }
        DB::commit();
        $count = count($request->ids);
        $this->toast('success', 'Success', "{$count} " . ($count === 1 ? 'product' : 'products') . " successfully deleted");
        return redirect()->route('admin.products.index');
    }

    /**
     * Get products for dropdown (API endpoint).
     */
    public function dropdown(Request $request)
    {
        $products = $this->productRepository->getProductsForDropdown();
        return response()->json([
            'products' => $products,
        ]);
    }
}

