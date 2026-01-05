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
import PriceListController from '@/actions/App/Http/Controllers/Admin/Price/PriceListController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    priceLists: {
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
            type: null,
            is_default: null,
            search: null,
        })
    }
});

const searchTerm = ref(props.filters?.search || '');
const selectedPriceLists = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Filter drawer state
const showFilterDrawer = ref(false);

// Filter state
const filters = ref({
    isActive: props.filters?.is_active ? (Array.isArray(props.filters.is_active) ? props.filters.is_active : [props.filters.is_active]) : [],
    type: props.filters?.type ? (Array.isArray(props.filters.type) ? props.filters.type : [props.filters.type]) : [],
    isDefault: props.filters?.is_default ? (Array.isArray(props.filters.is_default) ? props.filters.is_default : [props.filters.is_default]) : [],
});

// Filter options
const statusOptions = [
    { label: 'Active', value: true },
    { label: 'Inactive', value: false },
];

const typeOptions = [
    { label: 'Wholesale', value: 'wholesale' },
    { label: 'Retail', value: 'retail' },
    { label: 'Custom', value: 'custom' },
    { label: 'Promotional', value: 'promotional' },
];

const defaultOptions = [
    { label: 'Default', value: true },
    { label: 'Not Default', value: false },
];

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deletePriceListId = ref(null);
const deletePriceListName = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

const filteredPriceLists = computed(() => {
    return props.priceLists;
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Price Lists',
        href: PriceListController.index.url(),
    },
]);

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleView = (priceListId) => {
    router.visit(PriceListController.show.url(priceListId));
};

const handleEdit = (priceListId) => {
    router.visit(PriceListController.edit.url(priceListId));
};

const handleDelete = (priceListId, priceListName) => {
    deletePriceListId.value = priceListId;
    deletePriceListName.value = priceListName;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deletePriceListId.value) return;

    deleteLoading.value = true;

    router.delete(PriceListController.destroy.url(deletePriceListId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deletePriceListId.value = null;
            deletePriceListName.value = '';
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
    deletePriceListId.value = null;
    deletePriceListName.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deletePriceListName.value) {
        return `Are you sure you want to delete "${deletePriceListName.value}"?`;
    }
    return 'Are you sure you want to delete this price list?';
});

const handleCreate = () => {
    router.visit(PriceListController.create.url());
};

const handleToggleStatus = (priceListId) => {
    router.post(PriceListController.toggleStatus.url(priceListId), {}, {
        preserveScroll: true,
    });
};

const handleSetAsDefault = (priceListId) => {
    router.post(PriceListController.setAsDefault.url(priceListId), {}, {
        preserveScroll: true,
    });
};

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.isActive && filters.value.isActive.length > 0) count++;
    if (filters.value.type && filters.value.type.length > 0) count++;
    if (filters.value.isDefault && filters.value.isDefault.length > 0) count++;
    return count;
});

// Apply filters
const applyFilters = (closeDrawer = true) => {
    const params = {};
    
    if (filters.value.isActive && filters.value.isActive.length > 0) {
        params.is_active = filters.value.isActive;
    }
    if (filters.value.type && filters.value.type.length > 0) {
        params.type = filters.value.type;
    }
    if (filters.value.isDefault && filters.value.isDefault.length > 0) {
        params.is_default = filters.value.isDefault;
    }
    if (searchTerm.value) {
        params.search = searchTerm.value;
    }

    router.get(PriceListController.index.url(), params, {
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
        type: [],
        isDefault: [],
    };
    searchTerm.value = '';
    router.get(PriceListController.index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showFilterDrawer.value = false;
        }
    });
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedPriceLists.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedPriceLists.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedPriceLists.value.map(item => item.id);

    router.post(PriceListController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedPriceLists.value = [];
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
    const count = selectedPriceLists.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'price list' : 'price lists'}?`;
});

const hasSelectedPriceLists = computed(() => {
    return selectedPriceLists.value && selectedPriceLists.value.length > 0;
});

const getTypeSeverity = (type) => {
    const severityMap = {
        'wholesale': 'info',
        'retail': 'success',
        'custom': 'warning',
        'promotional': 'danger',
    };
    return severityMap[type] || 'secondary';
};
</script>

<template>
    <Head title="Price Lists" />

    <AppSidebarLayout title="Price Lists" :breadcrumbs="breadcrumbItems">
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
                            v-if="hasSelectedPriceLists"
                            label="Delete Selected" 
                            icon="pi pi-trash" 
                            severity="danger" 
                            outlined
                            class="flex-shrink-0"
                            @click="handleBulkDelete"
                        />
                        <span 
                            v-if="hasSelectedPriceLists" 
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;"
                        >
                            {{ selectedPriceLists.length }}
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
                    <Button label="Add Price List" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground mb-1">Total</div>
                    <div class="text-2xl font-bold">{{ statistics.total || 0 }}</div>
                </div>
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground mb-1">Active</div>
                    <div class="text-2xl font-bold text-green-600">{{ statistics.active || 0 }}</div>
                </div>
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground mb-1">Default</div>
                    <div class="text-2xl font-bold text-blue-600">{{ statistics.default || 0 }}</div>
                </div>
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground mb-1">Wholesale</div>
                    <div class="text-2xl font-bold text-purple-600">{{ statistics.wholesale || 0 }}</div>
                </div>
            </div>

            <!-- Price Lists Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredPriceLists.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No price lists found</p>
                    </div>

                    <DataTable v-else :value="filteredPriceLists" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedPriceLists"
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

                        <Column field="name" header="Name" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ slotProps.data.name }}</span>
                                    <span class="text-xs text-gray-500">{{ slotProps.data.slug }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="type" header="Type" :sortable="true" style="width: 120px">
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.type" :severity="getTypeSeverity(slotProps.data.type)" />
                            </template>
                        </Column>

                        <Column field="currency" header="Currency" style="width: 100px">
                            <template #body="slotProps">
                                <span class="text-sm">{{ slotProps.data.currency || 'USD' }}</span>
                            </template>
                        </Column>

                        <Column field="priority" header="Priority" style="width: 100px">
                            <template #body="slotProps">
                                <span class="text-sm">{{ slotProps.data.priority }}</span>
                            </template>
                        </Column>

                        <Column header="Status" style="width: 120px">
                            <template #body="slotProps">
                                <div class="flex items-center gap-2">
                                    <InputSwitch 
                                        v-model="slotProps.data.is_active" 
                                        @change="handleToggleStatus(slotProps.data.id)"
                                    />
                                    <Tag 
                                        v-if="slotProps.data.is_default" 
                                        value="Default" 
                                        severity="info"
                                    />
                                </div>
                            </template>
                        </Column>

                        <Column header="Rules" style="width: 100px">
                            <template #body="slotProps">
                                <span class="text-sm">{{ slotProps.data.price_rules?.length || 0 }}</span>
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
                                    <button 
                                        v-if="!slotProps.data.is_default"
                                        @click="handleSetAsDefault(slotProps.data.id)" 
                                        class="text-green-600 cursor-pointer"
                                        v-tooltip.top="'Set as Default'"
                                    >
                                        <i class="pi pi-star text-sm"></i>
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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Price List" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal 
            v-model:visible="showBulkDeleteModal" 
            title="Delete Price Lists" 
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

                <!-- Type Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Type</label>
                    <MultiSelect 
                        v-model="filters.type" 
                        :options="typeOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Type"
                        class="w-full"
                        display="chip"
                        :showClear="true"
                    />
                </div>

                <!-- Default Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Default</label>
                    <MultiSelect 
                        v-model="filters.isDefault" 
                        :options="defaultOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Default"
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

