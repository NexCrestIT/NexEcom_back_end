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
import OptionController from '@/actions/App/Http/Controllers/Admin/Option/OptionController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    options: {
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
            is_required: null,
            type: null,
            search: null,
        })
    }
});

const searchTerm = ref(props.filters?.search || '');
const selectedOptions = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Filter drawer state
const showFilterDrawer = ref(false);

// Filter state - initialize from props
const filters = ref({
    isActive: props.filters?.is_active ? (Array.isArray(props.filters.is_active) ? props.filters.is_active : [props.filters.is_active]) : [],
    isRequired: props.filters?.is_required ? (Array.isArray(props.filters.is_required) ? props.filters.is_required : [props.filters.is_required]) : [],
    type: props.filters?.type ? (Array.isArray(props.filters.type) ? props.filters.type : [props.filters.type]) : [],
});

// Filter options
const statusOptions = [
    { label: 'Active', value: true },
    { label: 'Inactive', value: false },
];

const requiredOptions = [
    { label: 'Required', value: true },
    { label: 'Optional', value: false },
];

const typeOptions = [
    { label: 'Text', value: 'text' },
    { label: 'Select', value: 'select' },
    { label: 'Multiselect', value: 'multiselect' },
    { label: 'Radio', value: 'radio' },
    { label: 'Checkbox', value: 'checkbox' },
];

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deleteOptionId = ref(null);
const deleteOptionName = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

// Options are already filtered on the backend
const filteredOptions = computed(() => {
    return props.options;
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Options',
        href: OptionController.index.url(),
    },
]);

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleView = (optionId) => {
    router.visit(OptionController.show.url(optionId));
};

const handleEdit = (optionId) => {
    router.visit(OptionController.edit.url(optionId));
};

const handleDelete = (optionId, optionName) => {
    deleteOptionId.value = optionId;
    deleteOptionName.value = optionName;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteOptionId.value) return;

    deleteLoading.value = true;

    router.delete(OptionController.destroy.url(deleteOptionId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteOptionId.value = null;
            deleteOptionName.value = '';
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
    deleteOptionId.value = null;
    deleteOptionName.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteOptionName.value) {
        return `Are you sure you want to delete "${deleteOptionName.value}"?`;
    }
    return 'Are you sure you want to delete this option?';
});

const handleCreate = () => {
    router.visit(OptionController.create.url());
};

const handleToggleStatus = (optionId) => {
    router.post(OptionController.toggleStatus.url(optionId), {}, {
        preserveScroll: true,
    });
};

const handleToggleRequired = (optionId) => {
    router.post(OptionController.toggleRequired.url(optionId), {}, {
        preserveScroll: true,
    });
};

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.isActive && filters.value.isActive.length > 0) count++;
    if (filters.value.isRequired && filters.value.isRequired.length > 0) count++;
    if (filters.value.type && filters.value.type.length > 0) count++;
    return count;
});

// Apply filters - send to backend
const applyFilters = (closeDrawer = true) => {
    const params = {};
    
    if (filters.value.isActive && filters.value.isActive.length > 0) {
        params.is_active = filters.value.isActive;
    }
    if (filters.value.isRequired && filters.value.isRequired.length > 0) {
        params.is_required = filters.value.isRequired;
    }
    if (filters.value.type && filters.value.type.length > 0) {
        params.type = filters.value.type;
    }
    if (searchTerm.value) {
        params.search = searchTerm.value;
    }

    router.get(OptionController.index.url(), params, {
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
        isRequired: [],
        type: [],
    };
    searchTerm.value = '';
    router.get(OptionController.index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showFilterDrawer.value = false;
        }
    });
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedOptions.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedOptions.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedOptions.value.map(option => option.id);

    router.post(OptionController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedOptions.value = [];
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
    const count = selectedOptions.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'option' : 'options'}?`;
});

const hasSelectedOptions = computed(() => {
    return selectedOptions.value && selectedOptions.value.length > 0;
});

const getTypeSeverity = (type) => {
    const severityMap = {
        'text': 'info',
        'select': 'success',
        'multiselect': 'warning',
        'radio': 'help',
        'checkbox': 'secondary',
    };
    return severityMap[type] || 'secondary';
};
</script>

<template>
    <Head title="Options" />

    <AppSidebarLayout title="Options" :breadcrumbs="breadcrumbItems">
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
                            v-if="hasSelectedOptions"
                            label="Delete Selected" 
                            icon="pi pi-trash" 
                            severity="danger" 
                            outlined
                            class="flex-shrink-0"
                            @click="handleBulkDelete"
                        />
                        <span 
                            v-if="hasSelectedOptions" 
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;"
                        >
                            {{ selectedOptions.length }}
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
                    <Button label="Add Option" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Options Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredOptions.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No options found</p>
                    </div>

                    <DataTable v-else :value="filteredOptions" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedOptions"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}" class="p-datatable-sm"
                        stripedRows dataKey="id" :globalFilterFields="['name', 'description']"
                        @page="onPage">
                        <Column selectionMode="multiple" headerStyle="width: 3rem" />

                        <Column header="#" style="width: 60px">
                            <template #body="slotProps">
                                <div class="flex items-center h-full">
                                    <span class="text-sm text-gray-600">{{ getRowNumber(slotProps.index) }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="name" header="Option Name" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ slotProps.data.name }}</span>
                                    <span v-if="slotProps.data.description" class="text-xs text-gray-500 truncate max-w-xs">
                                        {{ slotProps.data.description }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column field="type" header="Type" :sortable="true" style="width: 120px">
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.type" :severity="getTypeSeverity(slotProps.data.type)" />
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

                        <Column field="is_required" header="Required" :sortable="true" sortField="is_required" style="width: 120px">
                            <template #body="slotProps">
                                <InputSwitch 
                                    :modelValue="slotProps.data.is_required" 
                                    @update:modelValue="handleToggleRequired(slotProps.data.id)"
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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Option" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal 
            v-model:visible="showBulkDeleteModal" 
            title="Delete Options" 
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

                <!-- Required Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Required</label>
                    <MultiSelect 
                        v-model="filters.isRequired" 
                        :options="requiredOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Required"
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

