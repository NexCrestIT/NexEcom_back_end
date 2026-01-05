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
import DiscountController from '@/actions/App/Http/Controllers/Admin/Discount/DiscountController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    discounts: {
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
            search: null,
        })
    }
});

const searchTerm = ref(props.filters?.search || '');
const selectedDiscounts = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Filter drawer state
const showFilterDrawer = ref(false);

// Filter state - initialize from props
const filters = ref({
    isActive: props.filters?.is_active ? (Array.isArray(props.filters.is_active) ? props.filters.is_active : [props.filters.is_active]) : [],
    type: props.filters?.type ? (Array.isArray(props.filters.type) ? props.filters.type : [props.filters.type]) : [],
});

// Filter options
const statusOptions = [
    { label: 'Active', value: true },
    { label: 'Inactive', value: false },
];

const typeOptions = [
    { label: 'Percentage', value: 'percentage' },
    { label: 'Fixed', value: 'fixed' },
];

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deleteDiscountId = ref(null);
const deleteDiscountName = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

// Discounts are already filtered on the backend
const filteredDiscounts = computed(() => {
    return props.discounts;
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Discounts',
        href: DiscountController.index.url(),
    },
]);

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleView = (discountId) => {
    router.visit(DiscountController.show.url(discountId));
};

const handleEdit = (discountId) => {
    router.visit(DiscountController.edit.url(discountId));
};

const handleDelete = (discountId, discountName) => {
    deleteDiscountId.value = discountId;
    deleteDiscountName.value = discountName;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteDiscountId.value) return;

    deleteLoading.value = true;

    router.delete(DiscountController.destroy.url(deleteDiscountId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteDiscountId.value = null;
            deleteDiscountName.value = '';
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
    deleteDiscountId.value = null;
    deleteDiscountName.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteDiscountName.value) {
        return `Are you sure you want to delete "${deleteDiscountName.value}"?`;
    }
    return 'Are you sure you want to delete this discount?';
});

const handleCreate = () => {
    router.visit(DiscountController.create.url());
};

const handleToggleStatus = (discountId) => {
    router.post(DiscountController.toggleStatus.url(discountId), {}, {
        preserveScroll: true,
    });
};

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.isActive && filters.value.isActive.length > 0) count++;
    if (filters.value.type && filters.value.type.length > 0) count++;
    return count;
});

// Apply filters - send to backend
const applyFilters = (closeDrawer = true) => {
    const params = {};
    
    if (filters.value.isActive && filters.value.isActive.length > 0) {
        params.is_active = filters.value.isActive;
    }
    if (filters.value.type && filters.value.type.length > 0) {
        params.type = filters.value.type;
    }
    if (searchTerm.value) {
        params.search = searchTerm.value;
    }

    router.get(DiscountController.index.url(), params, {
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
    };
    searchTerm.value = '';
    router.get(DiscountController.index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showFilterDrawer.value = false;
        }
    });
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedDiscounts.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedDiscounts.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedDiscounts.value.map(discount => discount.id);

    router.post(DiscountController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedDiscounts.value = [];
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
    const count = selectedDiscounts.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'discount' : 'discounts'}?`;
});

const hasSelectedDiscounts = computed(() => {
    return selectedDiscounts.value && selectedDiscounts.value.length > 0;
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatValue = (discount) => {
    if (discount.type === 'percentage') {
        return `${discount.value}%`;
    }
    return `$${parseFloat(discount.value).toFixed(2)}`;
};

const isExpired = (discount) => {
    return new Date(discount.end_date) < new Date();
};

const isValid = (discount) => {
    if (!discount.is_active) return false;
    const now = new Date();
    const start = new Date(discount.start_date);
    const end = new Date(discount.end_date);
    return now >= start && now <= end;
};
</script>

<template>
    <Head title="Discounts" />

    <AppSidebarLayout title="Discounts" :breadcrumbs="breadcrumbItems">
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
                            v-if="hasSelectedDiscounts"
                            label="Delete Selected" 
                            icon="pi pi-trash" 
                            severity="danger" 
                            outlined
                            class="flex-shrink-0"
                            @click="handleBulkDelete"
                        />
                        <span 
                            v-if="hasSelectedDiscounts" 
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;"
                        >
                            {{ selectedDiscounts.length }}
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
                    <Button label="Add Discount" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Discounts Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredDiscounts.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No discounts found</p>
                    </div>

                    <DataTable v-else :value="filteredDiscounts" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedDiscounts"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}" class="p-datatable-sm"
                        stripedRows dataKey="id" :globalFilterFields="['name', 'code', 'description']"
                        @page="onPage">
                        <Column selectionMode="multiple" headerStyle="width: 3rem" />

                        <Column header="#" style="width: 60px">
                            <template #body="slotProps">
                                <div class="flex items-center h-full">
                                    <span class="text-sm text-gray-600">{{ getRowNumber(slotProps.index) }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="name" header="Discount Name" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900">{{ slotProps.data.name }}</span>
                                        <Tag 
                                            :value="slotProps.data.code" 
                                            severity="info"
                                            class="text-xs"
                                        />
                                    </div>
                                    <span v-if="slotProps.data.description" class="text-xs text-gray-500 truncate max-w-xs">
                                        {{ slotProps.data.description }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column field="type" header="Type" :sortable="true" style="width: 100px">
                            <template #body="slotProps">
                                <Tag 
                                    :value="slotProps.data.type === 'percentage' ? 'Percentage' : 'Fixed'" 
                                    :severity="slotProps.data.type === 'percentage' ? 'success' : 'warning'"
                                />
                            </template>
                        </Column>

                        <Column header="Value" style="width: 100px">
                            <template #body="slotProps">
                                <span class="font-medium">{{ formatValue(slotProps.data) }}</span>
                            </template>
                        </Column>

                        <Column header="Validity" style="width: 200px">
                            <template #body="slotProps">
                                <div class="flex flex-col text-xs">
                                    <span :class="isValid(slotProps.data) ? 'text-green-600' : 'text-gray-500'">
                                        {{ formatDate(slotProps.data.start_date) }} - {{ formatDate(slotProps.data.end_date) }}
                                    </span>
                                    <Tag 
                                        v-if="isExpired(slotProps.data)"
                                        value="Expired"
                                        severity="danger"
                                        class="text-xs mt-1"
                                    />
                                    <Tag 
                                        v-else-if="isValid(slotProps.data)"
                                        value="Active"
                                        severity="success"
                                        class="text-xs mt-1"
                                    />
                                </div>
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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Discount" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal 
            v-model:visible="showBulkDeleteModal" 
            title="Delete Discounts" 
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

