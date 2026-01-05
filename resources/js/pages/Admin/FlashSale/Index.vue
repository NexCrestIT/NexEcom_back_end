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
import FlashSaleController from '@/actions/App/Http/Controllers/Admin/FlashSale/FlashSaleController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    flashSales: {
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
            status: null,
            search: null,
        })
    }
});

const searchTerm = ref(props.filters?.search || '');
const selectedFlashSales = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Filter drawer state
const showFilterDrawer = ref(false);

// Filter state - initialize from props
const filters = ref({
    isActive: props.filters?.is_active ? (Array.isArray(props.filters.is_active) ? props.filters.is_active : [props.filters.is_active]) : [],
    isFeatured: props.filters?.is_featured ? (Array.isArray(props.filters.is_featured) ? props.filters.is_featured : [props.filters.is_featured]) : [],
    status: props.filters?.status ? (Array.isArray(props.filters.status) ? props.filters.status : [props.filters.status]) : [],
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

const saleStatusOptions = [
    { label: 'Ongoing', value: 'ongoing' },
    { label: 'Upcoming', value: 'upcoming' },
    { label: 'Expired', value: 'expired' },
];

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deleteFlashSaleId = ref(null);
const deleteFlashSaleName = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

// Flash sales are already filtered on the backend
const filteredFlashSales = computed(() => {
    return props.flashSales;
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Flash Sales',
        href: FlashSaleController.index.url(),
    },
]);

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleView = (flashSaleId) => {
    router.visit(FlashSaleController.show.url(flashSaleId));
};

const handleEdit = (flashSaleId) => {
    router.visit(FlashSaleController.edit.url(flashSaleId));
};

const handleDelete = (flashSaleId, flashSaleName) => {
    deleteFlashSaleId.value = flashSaleId;
    deleteFlashSaleName.value = flashSaleName;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteFlashSaleId.value) return;

    deleteLoading.value = true;

    router.delete(FlashSaleController.destroy.url(deleteFlashSaleId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteFlashSaleId.value = null;
            deleteFlashSaleName.value = '';
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
    deleteFlashSaleId.value = null;
    deleteFlashSaleName.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteFlashSaleName.value) {
        return `Are you sure you want to delete "${deleteFlashSaleName.value}"?`;
    }
    return 'Are you sure you want to delete this flash sale?';
});

const handleCreate = () => {
    router.visit(FlashSaleController.create.url());
};

const handleToggleStatus = (flashSaleId) => {
    router.post(FlashSaleController.toggleStatus.url(flashSaleId), {}, {
        preserveScroll: true,
    });
};

const handleToggleFeatured = (flashSaleId) => {
    router.post(FlashSaleController.toggleFeatured.url(flashSaleId), {}, {
        preserveScroll: true,
    });
};

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.isActive && filters.value.isActive.length > 0) count++;
    if (filters.value.isFeatured && filters.value.isFeatured.length > 0) count++;
    if (filters.value.status && filters.value.status.length > 0) count++;
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
    if (filters.value.status && filters.value.status.length > 0) {
        params.status = filters.value.status;
    }
    if (searchTerm.value) {
        params.search = searchTerm.value;
    }

    router.get(FlashSaleController.index.url(), params, {
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
        status: [],
    };
    searchTerm.value = '';
    router.get(FlashSaleController.index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showFilterDrawer.value = false;
        }
    });
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedFlashSales.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedFlashSales.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedFlashSales.value.map(flashSale => flashSale.id);

    router.post(FlashSaleController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedFlashSales.value = [];
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
    const count = selectedFlashSales.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'flash sale' : 'flash sales'}?`;
});

const hasSelectedFlashSales = computed(() => {
    return selectedFlashSales.value && selectedFlashSales.value.length > 0;
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDiscount = (flashSale) => {
    if (!flashSale.discount_value) return 'N/A';
    if (flashSale.discount_type === 'percentage') {
        return `${flashSale.discount_value}%`;
    }
    return `$${parseFloat(flashSale.discount_value).toFixed(2)}`;
};

const getStatusTag = (flashSale) => {
    const now = new Date();
    const start = new Date(flashSale.start_date);
    const end = new Date(flashSale.end_date);
    
    if (now < start) {
        return { label: 'Upcoming', severity: 'info' };
    } else if (now > end) {
        return { label: 'Expired', severity: 'danger' };
    } else if (flashSale.is_active) {
        return { label: 'Ongoing', severity: 'success' };
    } else {
        return { label: 'Inactive', severity: 'secondary' };
    }
};
</script>

<template>
    <Head title="Flash Sales" />

    <AppSidebarLayout title="Flash Sales" :breadcrumbs="breadcrumbItems">
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
                            v-if="hasSelectedFlashSales"
                            label="Delete Selected" 
                            icon="pi pi-trash" 
                            severity="danger" 
                            outlined
                            class="flex-shrink-0"
                            @click="handleBulkDelete"
                        />
                        <span 
                            v-if="hasSelectedFlashSales" 
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;"
                        >
                            {{ selectedFlashSales.length }}
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
                    <Button label="Add Flash Sale" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Flash Sales Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredFlashSales.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No flash sales found</p>
                    </div>

                    <DataTable v-else :value="filteredFlashSales" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedFlashSales"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}" class="p-datatable-sm"
                        stripedRows dataKey="id" :globalFilterFields="['name', 'slug', 'description']"
                        @page="onPage">
                        <Column selectionMode="multiple" headerStyle="width: 3rem" />

                        <Column header="#" style="width: 60px">
                            <template #body="slotProps">
                                <div class="flex items-center h-full">
                                    <span class="text-sm text-gray-600">{{ getRowNumber(slotProps.index) }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="name" header="Flash Sale Name" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900">{{ slotProps.data.name }}</span>
                                        <Tag 
                                            v-if="slotProps.data.is_featured"
                                            value="Featured"
                                            severity="warning"
                                            class="text-xs"
                                        />
                                    </div>
                                    <span v-if="slotProps.data.description" class="text-xs text-gray-500 truncate max-w-xs">
                                        {{ slotProps.data.description }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column header="Discount" style="width: 120px">
                            <template #body="slotProps">
                                <span class="font-medium">{{ formatDiscount(slotProps.data) }}</span>
                            </template>
                        </Column>

                        <Column header="Validity" style="width: 250px">
                            <template #body="slotProps">
                                <div class="flex flex-col text-xs">
                                    <span class="text-gray-600">
                                        {{ formatDate(slotProps.data.start_date) }}
                                    </span>
                                    <span class="text-gray-600">
                                        to {{ formatDate(slotProps.data.end_date) }}
                                    </span>
                                    <Tag 
                                        :value="getStatusTag(slotProps.data).label"
                                        :severity="getStatusTag(slotProps.data).severity"
                                        class="text-xs mt-1"
                                    />
                                </div>
                            </template>
                        </Column>

                        <Column header="Products" style="width: 100px">
                            <template #body="slotProps">
                                <span class="text-sm text-gray-600">
                                    {{ slotProps.data.products?.length || 0 }}
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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Flash Sale" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal 
            v-model:visible="showBulkDeleteModal" 
            title="Delete Flash Sales" 
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

                <!-- Sale Status Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Sale Status</label>
                    <MultiSelect 
                        v-model="filters.status" 
                        :options="saleStatusOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Sale Status"
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

