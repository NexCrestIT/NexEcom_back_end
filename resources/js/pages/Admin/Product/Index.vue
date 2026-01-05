<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputSwitch from 'primevue/inputswitch';
import Sidebar from 'primevue/sidebar';
import MultiSelect from 'primevue/multiselect';
import Tag from 'primevue/tag';
import { computed, ref } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/Admin/Product/ProductController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    statistics: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({
            is_active: null,
            is_featured: null,
            category_id: null,
            brand_id: null,
            collection_id: null,
            stock_status: null,
            search: null,
        })
    }
});

const searchTerm = ref(props.filters?.search || '');
const selectedProducts = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Filter drawer state
const showFilterDrawer = ref(false);

// Filter state - initialize from props
const filters = ref({
    isActive: props.filters?.is_active ? (Array.isArray(props.filters.is_active) ? props.filters.is_active : [props.filters.is_active]) : [],
    isFeatured: props.filters?.is_featured ? (Array.isArray(props.filters.is_featured) ? props.filters.is_featured : [props.filters.is_featured]) : [],
    categoryId: props.filters?.category_id ? (Array.isArray(props.filters.category_id) ? props.filters.category_id : [props.filters.category_id]) : [],
    brandId: props.filters?.brand_id ? (Array.isArray(props.filters.brand_id) ? props.filters.brand_id : [props.filters.brand_id]) : [],
    collectionId: props.filters?.collection_id ? (Array.isArray(props.filters.collection_id) ? props.filters.collection_id : [props.filters.collection_id]) : [],
    stockStatus: props.filters?.stock_status ? (Array.isArray(props.filters.stock_status) ? props.filters.stock_status : [props.filters.stock_status]) : [],
});

// Filter options
const statusOptions = [
    { label: 'Active', value: true },
    { label: 'Inactive', value: false },
];

const featuredOptions = [
    { label: 'Featured', value: true },
    { label: 'Not Featured', value: false },
];

const stockStatusOptions = [
    { label: 'In Stock', value: 'in_stock' },
    { label: 'Out of Stock', value: 'out_of_stock' },
    { label: 'Low Stock', value: 'low_stock' },
];

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deleteProductId = ref(null);
const deleteProductName = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

// Products are already filtered on the backend
const filteredProducts = computed(() => {
    return props.products;
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Products',
        href: ProductController.index.url(),
    },
]);

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleView = (productId) => {
    router.visit(ProductController.show.url(productId));
};

const handleEdit = (productId) => {
    router.visit(ProductController.edit.url(productId));
};

const handleDelete = (productId, productName) => {
    deleteProductId.value = productId;
    deleteProductName.value = productName;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteProductId.value) return;

    deleteLoading.value = true;

    router.delete(ProductController.destroy.url(deleteProductId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteProductId.value = null;
            deleteProductName.value = '';
        },
        onError: () => {
            deleteLoading.value = false;
        },
        onFinish: () => {
            deleteLoading.value = false;
        }
    });
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteProductId.value = null;
    deleteProductName.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteProductName.value) {
        return `Are you sure you want to delete "${deleteProductName.value}"?`;
    }
    return 'Are you sure you want to delete this product?';
});

const handleCreate = () => {
    router.visit(ProductController.create.url());
};

const handleToggleStatus = (productId) => {
    router.post(ProductController.toggleStatus.url(productId), {}, {
        preserveScroll: true,
    });
};

const handleToggleFeatured = (productId) => {
    router.post(ProductController.toggleFeatured.url(productId), {}, {
        preserveScroll: true,
    });
};

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.isActive && filters.value.isActive.length > 0) count++;
    if (filters.value.isFeatured && filters.value.isFeatured.length > 0) count++;
    if (filters.value.categoryId && filters.value.categoryId.length > 0) count++;
    if (filters.value.brandId && filters.value.brandId.length > 0) count++;
    if (filters.value.collectionId && filters.value.collectionId.length > 0) count++;
    if (filters.value.stockStatus && filters.value.stockStatus.length > 0) count++;
    return count;
});

// Apply filters - send to backend
const applyFilters = (closeDrawer = true) => {
    const params = {};
    
    if (filters.value.isActive && filters.value.isActive.length > 0) {
        params.is_active = filters.value.isActive;
    }
    if (filters.value.isFeatured && filters.value.isFeatured.length > 0) {
        params.is_featured = filters.value.isFeatured;
    }
    if (filters.value.categoryId && filters.value.categoryId.length > 0) {
        params.category_id = filters.value.categoryId;
    }
    if (filters.value.brandId && filters.value.brandId.length > 0) {
        params.brand_id = filters.value.brandId;
    }
    if (filters.value.collectionId && filters.value.collectionId.length > 0) {
        params.collection_id = filters.value.collectionId;
    }
    if (filters.value.stockStatus && filters.value.stockStatus.length > 0) {
        params.stock_status = filters.value.stockStatus;
    }
    if (searchTerm.value) {
        params.search = searchTerm.value;
    }

    router.get(ProductController.index.url(), params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            if (closeDrawer) {
                showFilterDrawer.value = false;
            }
        }
    });
};

// Reset all filters
const resetFilters = () => {
    filters.value = {
        isActive: [],
        isFeatured: [],
        categoryId: [],
        brandId: [],
        collectionId: [],
        stockStatus: [],
    };
    searchTerm.value = '';
    router.get(ProductController.index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showFilterDrawer.value = false;
        }
    });
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedProducts.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedProducts.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedProducts.value.map(product => product.id);

    router.post(ProductController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedProducts.value = [];
        },
        onError: () => {
            bulkDeleteLoading.value = false;
        },
        onFinish: () => {
            bulkDeleteLoading.value = false;
        }
    });
};

const cancelBulkDelete = () => {
    showBulkDeleteModal.value = false;
    bulkDeleteLoading.value = false;
};

const bulkDeleteMessage = computed(() => {
    const count = selectedProducts.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'product' : 'products'}?`;
});

const hasSelectedProducts = computed(() => {
    return selectedProducts.value && selectedProducts.value.length > 0;
});

const formatPrice = (price) => {
    return `$${parseFloat(price).toFixed(2)}`;
};

const getStockStatus = (product) => {
    if (!product.track_inventory) return { label: 'N/A', severity: 'secondary' };
    if (product.stock_quantity <= 0 && !product.allow_backorder) {
        return { label: 'Out of Stock', severity: 'danger' };
    }
    if (product.low_stock_threshold && product.stock_quantity <= product.low_stock_threshold) {
        return { label: 'Low Stock', severity: 'warning' };
    }
    return { label: 'In Stock', severity: 'success' };
};
</script>

<template>
    <Head title="Products" />

    <AppSidebarLayout title="Products" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Search and Actions -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="w-full sm:w-auto search-input-wrapper">
                    <i class="pi pi-search search-icon" />
                    <InputText 
                        v-model="searchTerm" 
                        placeholder="Search" 
                        class="w-full"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div class="flex gap-2">
                    <div class="relative inline-block">
                        <Button 
                            v-if="hasSelectedProducts"
                            label="Delete Selected" 
                            icon="pi pi-trash" 
                            severity="danger" 
                            outlined
                            class="flex-shrink-0"
                            @click="handleBulkDelete"
                        />
                        <span 
                            v-if="hasSelectedProducts" 
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;"
                        >
                            {{ selectedProducts.length }}
                        </span>
                    </div>
                    <div class="relative inline-block">
                        <Button 
                            label="Filters" 
                            icon="pi pi-filter" 
                            severity="secondary" 
                            outlined
                            class="flex-shrink-0"
                            @click="showFilterDrawer = true"
                        />
                        <span 
                            v-if="activeFiltersCount > 0" 
                            class="absolute -top-1 -right-1 bg-green-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;"
                        >
                            {{ activeFiltersCount }}
                        </span>
                    </div>
                    <Button label="Add Product" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Products Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredProducts.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No products found</p>
                    </div>

                    <DataTable v-else :value="filteredProducts" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedProducts"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}" class="p-datatable-sm"
                        stripedRows dataKey="id" :globalFilterFields="['name', 'sku', 'description']"
                        @page="onPage">
                        <Column selectionMode="multiple" headerStyle="width: 3rem" />

                        <Column header="#" style="width: 60px">
                            <template #body="slotProps">
                                <div class="flex items-center h-full">
                                    <span class="text-sm text-gray-600">{{ getRowNumber(slotProps.index) }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="name" header="Product Name" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex items-center h-full gap-3">
                                    <img 
                                        v-if="slotProps.data.main_image_url" 
                                        :src="slotProps.data.main_image_url" 
                                        :alt="slotProps.data.name"
                                        class="w-12 h-12 object-cover rounded"
                                        @error="$event.target.style.display = 'none'"
                                    />
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ slotProps.data.name }}</span>
                                        <span class="text-xs text-gray-500">SKU: {{ slotProps.data.sku }}</span>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column field="price" header="Price" :sortable="true" style="width: 100px">
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ formatPrice(slotProps.data.price) }}</span>
                                    <span v-if="slotProps.data.compare_at_price" class="text-xs text-gray-400 line-through">
                                        {{ formatPrice(slotProps.data.compare_at_price) }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column field="stock_quantity" header="Stock" :sortable="true" style="width: 100px">
                            <template #body="slotProps">
                                <Tag 
                                    :value="getStockStatus(slotProps.data).label" 
                                    :severity="getStockStatus(slotProps.data).severity"
                                />
                                <span class="text-xs text-gray-500 block mt-1">
                                    Qty: {{ slotProps.data.stock_quantity }}
                                </span>
                            </template>
                        </Column>

                        <Column field="is_active" header="Status" :sortable="true" sortField="is_active" style="width: 120px">
                            <template #body="slotProps">
                                <InputSwitch 
                                    :modelValue="slotProps.data.is_active" 
                                    @update:modelValue="handleToggleStatus(slotProps.data.id)"
                                />
                            </template>
                        </Column>

                        <Column field="is_featured" header="Featured" :sortable="true" sortField="is_featured" style="width: 120px">
                            <template #body="slotProps">
                                <InputSwitch 
                                    :modelValue="slotProps.data.is_featured" 
                                    @update:modelValue="handleToggleFeatured(slotProps.data.id)"
                                />
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 100px">
                            <template #body="slotProps">
                                <div class="flex items-center gap-3">
                                    <button @click="handleView(slotProps.data.id)" class="text-gray-600 cursor-pointer"
                                        v-tooltip.top="'View'">
                                        <i class="pi pi-eye text-sm"></i>
                                    </button>
                                    <button @click="handleEdit(slotProps.data.id)" class="text-blue-600 cursor-pointer"
                                        v-tooltip.top="'Edit'">
                                        <i class="pi pi-pencil text-sm"></i>
                                    </button>
                                    <button @click="handleDelete(slotProps.data.id, slotProps.data.name)"
                                        class="text-red-600 cursor-pointer" v-tooltip.top="'Delete'">
                                        <i class="pi pi-trash text-sm"></i>
                                    </button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Product" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal 
            v-model:visible="showBulkDeleteModal" 
            title="Delete Products" 
            :message="bulkDeleteMessage"
            :loading="bulkDeleteLoading" 
            @confirm="confirmBulkDelete"
            @cancel="cancelBulkDelete" 
        />

        <!-- Filter Drawer -->
        <Sidebar 
            v-model:visible="showFilterDrawer" 
            position="right" 
            class="w-full sm:w-96"
            :modal="true"
            :dismissable="true"
        >
            <template #header>
                <div class="flex items-center justify-between w-full pr-8">
                    <h2 class="text-xl font-semibold">Filters</h2>
                </div>
            </template>

            <div class="flex flex-col gap-6">
                <!-- Status Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Status</label>
                    <MultiSelect 
                        v-model="filters.isActive" 
                        :options="statusOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Status"
                        class="w-full"
                        display="chip"
                        :showClear="true"
                    />
                </div>

                <!-- Featured Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Featured</label>
                    <MultiSelect 
                        v-model="filters.isFeatured" 
                        :options="featuredOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Featured"
                        class="w-full"
                        display="chip"
                        :showClear="true"
                    />
                </div>

                <!-- Stock Status Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Stock Status</label>
                    <MultiSelect 
                        v-model="filters.stockStatus" 
                        :options="stockStatusOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Stock Status"
                        class="w-full"
                        display="chip"
                        :showClear="true"
                    />
                </div>

                <!-- Active Filters Count -->
                <div v-if="activeFiltersCount > 0" class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg">
                    <i class="pi pi-info-circle mr-2"></i>
                    {{ activeFiltersCount }} {{ activeFiltersCount === 1 ? 'filter' : 'filters' }} active
                </div>
            </div>

            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button 
                        label="Reset" 
                        severity="secondary" 
                        outlined
                        @click="resetFilters"
                    />
                    <Button 
                        label="Apply Filters" 
                        severity="primary"
                        @click="applyFilters"
                    />
                </div>
            </template>
        </Sidebar>
    </AppSidebarLayout>
</template>

