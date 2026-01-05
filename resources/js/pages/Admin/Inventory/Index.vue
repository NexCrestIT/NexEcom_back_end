<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Sidebar from 'primevue/sidebar';
import MultiSelect from 'primevue/multiselect';
import Tag from 'primevue/tag';
import { computed, ref } from 'vue';
import InventoryController from '@/actions/App/Http/Controllers/Admin/Inventory/InventoryController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    inventory: {
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
            product_id: null,
            location: null,
            stock_status: null,
            search: null,
        })
    }
});

const searchTerm = ref(props.filters?.search || '');
const selectedInventory = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Filter drawer state
const showFilterDrawer = ref(false);

// Filter state - initialize from props
const filters = ref({
    stockStatus: props.filters?.stock_status ? (Array.isArray(props.filters.stock_status) ? props.filters.stock_status : [props.filters.stock_status]) : [],
    location: props.filters?.location ? (Array.isArray(props.filters.location) ? props.filters.location : [props.filters.location]) : [],
});

// Filter options
const stockStatusOptions = [
    { label: 'In Stock', value: 'in_stock' },
    { label: 'Out of Stock', value: 'out_of_stock' },
    { label: 'Low Stock', value: 'low_stock' },
];

const locationOptions = [
    { label: 'Main', value: 'main' },
    { label: 'Warehouse 1', value: 'warehouse_1' },
    { label: 'Warehouse 2', value: 'warehouse_2' },
    { label: 'Store', value: 'store' },
];

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deleteInventoryId = ref(null);
const deleteInventoryName = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

// Inventory is already filtered on the backend
const filteredInventory = computed(() => {
    return props.inventory;
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Inventory',
        href: InventoryController.index.url(),
    },
]);

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleView = (inventoryId) => {
    router.visit(InventoryController.show.url(inventoryId));
};

const handleEdit = (inventoryId) => {
    router.visit(InventoryController.edit.url(inventoryId));
};

const handleDelete = (inventoryId, productName) => {
    deleteInventoryId.value = inventoryId;
    deleteInventoryName.value = productName;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteInventoryId.value) return;

    deleteLoading.value = true;

    router.delete(InventoryController.destroy.url(deleteInventoryId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteInventoryId.value = null;
            deleteInventoryName.value = '';
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
    deleteInventoryId.value = null;
    deleteInventoryName.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteInventoryName.value) {
        return `Are you sure you want to delete inventory for "${deleteInventoryName.value}"?`;
    }
    return 'Are you sure you want to delete this inventory item?';
});

const handleCreate = () => {
    router.visit(InventoryController.create.url());
};

const handleAdjustStock = (inventoryId) => {
    router.visit(InventoryController.show.url(inventoryId) + '?action=adjust');
};

const handleLowStock = () => {
    router.visit(InventoryController.lowStock.url());
};

const handleStockMovements = () => {
    router.visit(InventoryController.stockMovements.url());
};

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.stockStatus && filters.value.stockStatus.length > 0) count++;
    if (filters.value.location && filters.value.location.length > 0) count++;
    return count;
});

// Apply filters - send to backend
const applyFilters = (closeDrawer = true) => {
    const params = {};
    
    if (filters.value.stockStatus && filters.value.stockStatus.length > 0) {
        params.stock_status = filters.value.stockStatus;
    }
    if (filters.value.location && filters.value.location.length > 0) {
        params.location = filters.value.location;
    }
    if (searchTerm.value) {
        params.search = searchTerm.value;
    }

    router.get(InventoryController.index.url(), params, {
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
        stockStatus: [],
        location: [],
    };
    searchTerm.value = '';
    router.get(InventoryController.index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showFilterDrawer.value = false;
        }
    });
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedInventory.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedInventory.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedInventory.value.map(item => item.id);

    router.post(InventoryController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedInventory.value = [];
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
    const count = selectedInventory.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'inventory item' : 'inventory items'}?`;
});

const hasSelectedInventory = computed(() => {
    return selectedInventory.value && selectedInventory.value.length > 0;
});

const getStockStatus = (item) => {
    if (item.available_quantity <= 0) {
        return { label: 'Out of Stock', severity: 'danger' };
    } else if (item.is_low_stock) {
        return { label: 'Low Stock', severity: 'warning' };
    } else {
        return { label: 'In Stock', severity: 'success' };
    }
};
</script>

<template>
    <Head title="Inventory" />

    <AppSidebarLayout title="Inventory" :breadcrumbs="breadcrumbItems">
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
                    <Button 
                        label="Low Stock" 
                        icon="pi pi-exclamation-triangle" 
                        severity="warning" 
                        outlined
                        class="flex-shrink-0"
                        @click="handleLowStock"
                    />
                    <Button 
                        label="Stock Movements" 
                        icon="pi pi-list" 
                        severity="info" 
                        outlined
                        class="flex-shrink-0"
                        @click="handleStockMovements"
                    />
                    <div class="relative inline-block">
                        <Button 
                            v-if="hasSelectedInventory"
                            label="Delete Selected" 
                            icon="pi pi-trash" 
                            severity="danger" 
                            outlined
                            class="flex-shrink-0"
                            @click="handleBulkDelete"
                        />
                        <span 
                            v-if="hasSelectedInventory" 
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;"
                        >
                            {{ selectedInventory.length }}
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
                    <Button label="Add Inventory" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground mb-1">Total Items</div>
                    <div class="text-2xl font-bold">{{ statistics.total_items || 0 }}</div>
                </div>
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground mb-1">In Stock</div>
                    <div class="text-2xl font-bold text-green-600">{{ statistics.in_stock || 0 }}</div>
                </div>
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground mb-1">Out of Stock</div>
                    <div class="text-2xl font-bold text-red-600">{{ statistics.out_of_stock || 0 }}</div>
                </div>
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground mb-1">Low Stock</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ statistics.low_stock || 0 }}</div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredInventory.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No inventory items found</p>
                    </div>

                    <DataTable v-else :value="filteredInventory" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedInventory"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}" class="p-datatable-sm"
                        stripedRows dataKey="id" :globalFilterFields="['product.name', 'product.sku', 'location']"
                        @page="onPage">
                        <Column selectionMode="multiple" headerStyle="width: 3rem" />

                        <Column header="#" style="width: 60px">
                            <template #body="slotProps">
                                <div class="flex items-center h-full">
                                    <span class="text-sm text-gray-600">{{ getRowNumber(slotProps.index) }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="product.name" header="Product" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ slotProps.data.product?.name || 'N/A' }}</span>
                                    <span class="text-xs text-gray-500">{{ slotProps.data.product?.sku || 'N/A' }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="location" header="Location" :sortable="true" style="width: 120px">
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.location || 'main'" severity="info" />
                            </template>
                        </Column>

                        <Column header="Quantity" style="width: 100px">
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ slotProps.data.quantity }}</span>
                                    <span class="text-xs text-gray-500">Available: {{ slotProps.data.available_quantity }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column header="Reserved" style="width: 100px">
                            <template #body="slotProps">
                                <span class="text-sm">{{ slotProps.data.reserved_quantity || 0 }}</span>
                            </template>
                        </Column>

                        <Column header="Status" style="width: 120px">
                            <template #body="slotProps">
                                <Tag 
                                    :value="getStockStatus(slotProps.data).label" 
                                    :severity="getStockStatus(slotProps.data).severity"
                                />
                            </template>
                        </Column>

                        <Column header="Cost Price" style="width: 120px">
                            <template #body="slotProps">
                                <span v-if="slotProps.data.cost_price" class="text-sm">
                                    ${{ parseFloat(slotProps.data.cost_price).toFixed(2) }}
                                </span>
                                <span v-else class="text-sm text-gray-400">N/A</span>
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 150px">
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
                                    <button @click="handleAdjustStock(slotProps.data.id)" class="text-green-600 cursor-pointer"
                                        v-tooltip.top="'Adjust Stock'">
                                        <i class="pi pi-plus-minus text-sm"></i>
                                    </button>
                                    <button @click="handleDelete(slotProps.data.id, slotProps.data.product?.name)"
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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Inventory" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal 
            v-model:visible="showBulkDeleteModal" 
            title="Delete Inventory Items" 
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
                <!-- Stock Status Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Stock Status</label>
                    <MultiSelect 
                        v-model="filters.stockStatus" 
                        :options="stockStatusOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Status"
                        class="w-full"
                        display="chip"
                        :showClear="true"
                    />
                </div>

                <!-- Location Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Location</label>
                    <MultiSelect 
                        v-model="filters.location" 
                        :options="locationOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Location"
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

