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
import { computed, ref } from 'vue';
import CarouselController from '@/actions/App/Http/Controllers/Admin/Carousel/CarouselController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    carousels: {
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
            search: null,
        })
    }
});

const searchTerm = ref(props.filters?.search || '');
const selectedCarousels = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Filter drawer state
const showFilterDrawer = ref(false);

// Filter state
const filters = ref({
    isActive: props.filters?.is_active ? (Array.isArray(props.filters.is_active) ? props.filters.is_active : [props.filters.is_active]) : [],
});

// Filter options
const statusOptions = [
    { label: 'Active', value: true },
    { label: 'Inactive', value: false },
];

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deleteCarouselId = ref(null);
const deleteCarouselTitle = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

const filteredCarousels = computed(() => {
    return props.carousels;
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Carousels',
        href: CarouselController.index.url(),
    },
]);

const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.isActive && filters.value.isActive.length > 0) count++;
    return count;
});

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleCreate = () => {
    router.visit(CarouselController.create.url());
};

const handleView = (carouselId) => {
    router.visit(CarouselController.show.url(carouselId));
};

const handleEdit = (carouselId) => {
    router.visit(CarouselController.edit.url(carouselId));
};

const handleDelete = (carouselId, carouselTitle) => {
    deleteCarouselId.value = carouselId;
    deleteCarouselTitle.value = carouselTitle;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteCarouselId.value) return;

    deleteLoading.value = true;

    router.delete(CarouselController.destroy.url(deleteCarouselId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteCarouselId.value = null;
            deleteCarouselTitle.value = '';
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
    deleteCarouselId.value = null;
    deleteCarouselTitle.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteCarouselTitle.value) {
        return `Are you sure you want to delete the carousel "${deleteCarouselTitle.value}"? This action cannot be undone.`;
    }
    return 'Are you sure you want to delete this carousel? This action cannot be undone.';
});

const handleToggleStatus = (carouselId) => {
    router.post(CarouselController.toggleStatus.url(carouselId), {}, {
        preserveScroll: true,
    });
};

const handleBulkDelete = () => {
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    bulkDeleteLoading.value = true;

    const ids = selectedCarousels.value.map(carousel => carousel.id);

    router.post(CarouselController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedCarousels.value = [];
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
    const count = selectedCarousels.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'carousel' : 'carousels'}?`;
});

const hasSelectedCarousels = computed(() => {
    return selectedCarousels.value && selectedCarousels.value.length > 0;
});

const applyFilters = () => {
    const queryParams = new URLSearchParams();
    
    if (searchTerm.value) {
        queryParams.append('search', searchTerm.value);
    }
    
    if (filters.value.isActive && filters.value.isActive.length > 0) {
        filters.value.isActive.forEach(status => {
            queryParams.append('is_active[]', status);
        });
    }

    const url = `${CarouselController.index.url()}?${queryParams.toString()}`;
    router.visit(url, { preserveScroll: true });
};

const resetFilters = () => {
    searchTerm.value = '';
    filters.value.isActive = [];
    router.visit(CarouselController.index.url(), { preserveScroll: true });
};
</script>

<template>
    <Head title="Carousels" />

    <AppSidebarLayout title="Carousels" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Search and Actions -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="w-full sm:w-auto search-input-wrapper">
                    <i class="pi pi-search search-icon" />
                    <InputText 
                        v-model="searchTerm" 
                        placeholder="Search by title or subtitle" 
                        class="w-full"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div class="flex gap-2">
                    <div class="relative inline-block">
                        <Button 
                            v-if="hasSelectedCarousels"
                            label="Delete Selected" 
                            icon="pi pi-trash" 
                            severity="danger" 
                            outlined
                            class="flex-shrink-0"
                            @click="handleBulkDelete"
                        />
                        <span 
                            v-if="hasSelectedCarousels" 
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;"
                        >
                            {{ selectedCarousels.length }}
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
                    <Button label="Add Carousel" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Carousels Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredCarousels.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No carousels found</p>
                    </div>

                    <DataTable v-else :value="filteredCarousels" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedCarousels"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}" class="p-datatable-sm"
                        stripedRows dataKey="id" :globalFilterFields="['title', 'subtitle']"
                        @page="onPage">
                        <Column selectionMode="multiple" headerStyle="width: 3rem" />

                        <Column header="#" style="width: 60px">
                            <template #body="slotProps">
                                <div class="flex items-center h-full">
                                    <span class="text-sm text-gray-600">{{ getRowNumber(slotProps.index) }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="title" header="Title" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ slotProps.data.title }}</span>
                                    <span v-if="slotProps.data.subtitle" class="text-xs text-gray-500 truncate max-w-xs">
                                        {{ slotProps.data.subtitle }}
                                    </span>
                                </div>
                            </template>
                        </Column>

                        <Column field="button_name" header="Button" :sortable="true" style="width: 150px">
                            <template #body="slotProps">
                                <span v-if="slotProps.data.button_name" class="text-sm text-gray-700">{{ slotProps.data.button_name }}</span>
                                <span v-else class="text-xs text-gray-400">-</span>
                            </template>
                        </Column>

                        <Column field="image" header="Image" :sortable="true" style="width: 120px">
                            <template #body="slotProps">
                                <div class="truncate max-w-xs">
                                    <span class="text-xs text-gray-600">{{ slotProps.data.image }}</span>
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

                        <Column field="created_at" header="Created" :sortable="true" style="width: 150px">
                            <template #body="slotProps">
                                <span class="text-sm text-gray-600">{{ new Date(slotProps.data.created_at).toLocaleDateString() }}</span>
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
                                    <button @click="handleDelete(slotProps.data.id, slotProps.data.title)"
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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Carousel" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal 
            v-model:visible="showBulkDeleteModal" 
            title="Delete Carousels" 
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
                <div class="flex flex-col gap-3">
                    <label class="font-medium text-gray-700">Status</label>
                    <MultiSelect 
                        v-model="filters.isActive" 
                        :options="statusOptions" 
                        optionLabel="label" 
                        optionValue="value"
                        placeholder="Select status" 
                        class="w-full"
                    />
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4 border-t">
                    <Button label="Apply" icon="pi pi-check" severity="primary" class="flex-1" @click="applyFilters" />
                    <Button label="Reset" icon="pi pi-refresh" severity="secondary" class="flex-1" @click="resetFilters" />
                </div>
            </div>
        </Sidebar>
    </AppSidebarLayout>
</template>

<style scoped>
.search-input-wrapper {
    position: relative;
}

.search-input-wrapper .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 14px;
}

.search-input-wrapper :deep(input) {
    padding-left: 36px;
}

.data-table-container :deep(.p-datatable) {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
}

.data-table-container :deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #f9fafb;
    font-weight: 600;
    color: #374151;
    border-color: #e5e7eb;
}

.data-table-container :deep(.p-datatable .p-datatable-tbody > tr > td) {
    border-color: #e5e7eb;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.data-table-container :deep(.p-datatable-striped .p-datatable-tbody > tr:nth-child(odd)) {
    background-color: #ffffff;
}

.data-table-container :deep(.p-datatable-striped .p-datatable-tbody > tr:nth-child(even)) {
    background-color: #f9fafb;
}

.data-table-container :deep(.p-paginator) {
    background-color: #f9fafb;
    border-top: 1px solid #e5e7eb;
}
</style>
