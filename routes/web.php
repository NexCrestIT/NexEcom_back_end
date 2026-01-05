<?php

use App\Http\Controllers\Admin\Attribute\AttributeController;
use App\Http\Controllers\Admin\Brand\BrandController;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\Collection\CollectionController;
use App\Http\Controllers\Admin\Discount\DiscountController;
use App\Http\Controllers\Admin\FlashSale\FlashSaleController;
use App\Http\Controllers\Admin\Inventory\InventoryController;
use App\Http\Controllers\Admin\Label\LabelController;
use App\Http\Controllers\Admin\Price\PriceListController;
use App\Http\Controllers\Admin\Option\OptionController;
use App\Http\Controllers\Admin\Role\RoleController;
use App\Http\Controllers\Admin\Tag\TagController;
use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');




Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::resource('labels', LabelController::class)->names('labels');
    Route::post('labels/bulk-delete', [LabelController::class, 'bulkDelete'])->name('labels.bulk-delete');
    
    // Tag routes
    Route::resource('tags', TagController::class)->names('tags');
    Route::post('tags/{tag}/toggle-status', [TagController::class, 'toggleStatus'])->name('tags.toggle-status');
    Route::post('tags/bulk-delete', [TagController::class, 'bulkDelete'])->name('tags.bulk-delete');
    Route::get('tags-dropdown', [TagController::class, 'dropdown'])->name('tags.dropdown');
    
    // Brand routes
    Route::resource('brands', BrandController::class)->names('brands');
    Route::post('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggle-status');
    Route::post('brands/{brand}/toggle-featured', [BrandController::class, 'toggleFeatured'])->name('brands.toggle-featured');
    Route::post('brands/sort-order', [BrandController::class, 'updateSortOrder'])->name('brands.sort-order');
    Route::post('brands/bulk-delete', [BrandController::class, 'bulkDelete'])->name('brands.bulk-delete');
    Route::get('brands-dropdown', [BrandController::class, 'dropdown'])->name('brands.dropdown');
    
    // Collection routes
    Route::resource('collections', CollectionController::class)->names('collections');
    Route::post('collections/{collection}/toggle-status', [CollectionController::class, 'toggleStatus'])->name('collections.toggle-status');
    Route::post('collections/{collection}/toggle-featured', [CollectionController::class, 'toggleFeatured'])->name('collections.toggle-featured');
    Route::post('collections/sort-order', [CollectionController::class, 'updateSortOrder'])->name('collections.sort-order');
    Route::post('collections/bulk-delete', [CollectionController::class, 'bulkDelete'])->name('collections.bulk-delete');
    Route::get('collections-dropdown', [CollectionController::class, 'dropdown'])->name('collections.dropdown');
    
    // Option routes
    Route::resource('options', OptionController::class)->names('options');
    Route::post('options/{option}/toggle-status', [OptionController::class, 'toggleStatus'])->name('options.toggle-status');
    Route::post('options/{option}/toggle-required', [OptionController::class, 'toggleRequired'])->name('options.toggle-required');
    Route::post('options/sort-order', [OptionController::class, 'updateSortOrder'])->name('options.sort-order');
    Route::post('options/bulk-delete', [OptionController::class, 'bulkDelete'])->name('options.bulk-delete');
    Route::get('options-dropdown', [OptionController::class, 'dropdown'])->name('options.dropdown');
    
    // Discount routes
    Route::resource('discounts', DiscountController::class)->names('discounts');
    Route::post('discounts/{discount}/toggle-status', [DiscountController::class, 'toggleStatus'])->name('discounts.toggle-status');
    Route::post('discounts/sort-order', [DiscountController::class, 'updateSortOrder'])->name('discounts.sort-order');
    Route::post('discounts/bulk-delete', [DiscountController::class, 'bulkDelete'])->name('discounts.bulk-delete');
    Route::get('discounts-dropdown', [DiscountController::class, 'dropdown'])->name('discounts.dropdown');
    
    // Flash Sale routes
    Route::resource('flash-sales', FlashSaleController::class)->names('flash-sales');
    Route::post('flash-sales/{flashSale}/toggle-status', [FlashSaleController::class, 'toggleStatus'])->name('flash-sales.toggle-status');
    Route::post('flash-sales/{flashSale}/toggle-featured', [FlashSaleController::class, 'toggleFeatured'])->name('flash-sales.toggle-featured');
    Route::post('flash-sales/{flashSale}/sort-order', [FlashSaleController::class, 'updateSortOrder'])->name('flash-sales.sort-order');
    Route::post('flash-sales/bulk-delete', [FlashSaleController::class, 'bulkDelete'])->name('flash-sales.bulk-delete');
    Route::get('flash-sales-dropdown', [FlashSaleController::class, 'dropdown'])->name('flash-sales.dropdown');
    
    // Inventory routes
    Route::resource('inventory', InventoryController::class)->names('inventory');
    Route::post('inventory/{inventory}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventory.adjust-stock');
    Route::get('inventory/stock-movements', [InventoryController::class, 'stockMovements'])->name('inventory.stock-movements');
    Route::get('inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    Route::post('inventory/bulk-delete', [InventoryController::class, 'bulkDelete'])->name('inventory.bulk-delete');
    
    // Price List routes
    Route::resource('price-lists', PriceListController::class)->names('price-lists');
    Route::post('price-lists/{priceList}/toggle-status', [PriceListController::class, 'toggleStatus'])->name('price-lists.toggle-status');
    Route::post('price-lists/{priceList}/set-as-default', [PriceListController::class, 'setAsDefault'])->name('price-lists.set-as-default');
    Route::post('price-lists/sort-order', [PriceListController::class, 'updateSortOrder'])->name('price-lists.sort-order');
    Route::post('price-lists/bulk-delete', [PriceListController::class, 'bulkDelete'])->name('price-lists.bulk-delete');
    Route::get('price-lists-dropdown', [PriceListController::class, 'dropdown'])->name('price-lists.dropdown');
    
    // Product routes
    Route::resource('products', \App\Http\Controllers\Admin\Product\ProductController::class)->names('products');
    Route::post('products/{product}/toggle-status', [\App\Http\Controllers\Admin\Product\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::post('products/{product}/toggle-featured', [\App\Http\Controllers\Admin\Product\ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::post('products/{product}/update-stock', [\App\Http\Controllers\Admin\Product\ProductController::class, 'updateStock'])->name('products.update-stock');
    Route::post('products/sort-order', [\App\Http\Controllers\Admin\Product\ProductController::class, 'updateSortOrder'])->name('products.sort-order');
    Route::post('products/bulk-delete', [\App\Http\Controllers\Admin\Product\ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::get('products-dropdown', [\App\Http\Controllers\Admin\Product\ProductController::class, 'dropdown'])->name('products.dropdown');
    
    // Attribute routes
    Route::resource('attributes', AttributeController::class)->names('attributes');
    Route::post('attributes/{attribute}/toggle-status', [AttributeController::class, 'toggleStatus'])->name('attributes.toggle-status');
    Route::post('attributes/{attribute}/toggle-filterable', [AttributeController::class, 'toggleFilterable'])->name('attributes.toggle-filterable');
    Route::post('attributes/sort-order', [AttributeController::class, 'updateSortOrder'])->name('attributes.sort-order');
    Route::post('attributes/bulk-delete', [AttributeController::class, 'bulkDelete'])->name('attributes.bulk-delete');
    Route::get('attributes-dropdown', [AttributeController::class, 'dropdown'])->name('attributes.dropdown');
    
    Route::resource('users', UserController::class)->names('users');
    Route::resource('roles', RoleController::class)->names('roles');
    
    // Additional user routes
    Route::post('users/{id}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password');

    // Category routes
    Route::resource('categories', CategoryController::class)->names('categories');
    Route::post('categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::post('categories/{id}/toggle-featured', [CategoryController::class, 'toggleFeatured'])->name('categories.toggle-featured');
    Route::post('categories/sort-order', [CategoryController::class, 'updateSortOrder'])->name('categories.sort-order');
    Route::post('categories/{id}/move', [CategoryController::class, 'moveCategory'])->name('categories.move');
    Route::post('categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
    Route::post('categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
    Route::get('categories-search', [CategoryController::class, 'search'])->name('categories.search');
    Route::get('categories-dropdown', [CategoryController::class, 'dropdown'])->name('categories.dropdown');
});



require __DIR__ . '/settings.php';
