<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputSwitch from 'primevue/inputswitch';
import Checkbox from 'primevue/checkbox';
import Sidebar from 'primevue/sidebar';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import { computed, ref } from 'vue';
import CategoryController from '@/actions/App/Http/Controllers/Admin/Category/CategoryController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    categories: {
        type: [Array, Object],
        default: () => []
    },
    viewType: {
        type: String,
        default: 'list'
    },
    statistics: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({
            status: null,
            featured: null,
            parent_id: null,
            search: null,
        })
    },
    rootCategories: {
        type: Array,
        default: () => []
    }
});

const searchTerm = ref(props.filters?.search || '');
const expandedRows = ref({});
const selectedCategories = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Filter drawer state
const showFilterDrawer = ref(false);

// Filter state - initialize from props (support arrays for multi-select)
const filters = ref({
    status: props.filters?.status ? (Array.isArray(props.filters.status) ? props.filters.status : [props.filters.status]) : [],
    featured: props.filters?.featured ? (Array.isArray(props.filters.featured) ? props.filters.featured : [props.filters.featured]) : [],
    parentId: props.filters?.parent_id ? (Array.isArray(props.filters.parent_id) ? props.filters.parent_id : [props.filters.parent_id]) : [],
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

// Get unique parent categories for filter
const parentCategoryOptions = computed(() => {
    const options = [{ label: 'All Categories', value: null }];
    
    if (props.rootCategories && props.rootCategories.length > 0) {
        props.rootCategories.forEach(category => {
            options.push({ 
                label: category.name, 
                value: category.id 
            });
        });
    }
    
    return options;
});

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deleteCategoryId = ref(null);
const deleteCategoryName = ref('');
const deleteWarningMessage = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

// Extract data array from paginated response or use array directly
const categoriesList = computed(() => {
    if (Array.isArray(props.categories)) {
        return props.categories;
    }
    if (props.categories && props.categories.data) {
        return props.categories.data;
    }
    return [];
});

// Categories are already filtered on the backend, so just return the list
const filteredCategories = computed(() => {
    return categoriesList.value;
});

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.status && filters.value.status.length > 0) count++;
    if (filters.value.featured && filters.value.featured.length > 0) count++;
    if (filters.value.parentId && filters.value.parentId.length > 0) count++;
    return count;
});

// Apply filters - send to backend
const applyFilters = (closeDrawer = true) => {
    const params = {};
    
    if (filters.value.status && filters.value.status.length > 0) {
        params.status = filters.value.status;
    }
    if (filters.value.featured && filters.value.featured.length > 0) {
        params.featured = filters.value.featured;
    }
    if (filters.value.parentId && filters.value.parentId.length > 0) {
        params.parent_id = filters.value.parentId;
    }
    if (searchTerm.value) {
        params.search = searchTerm.value;
    }

    router.get(CategoryController.index.url(), params, {
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
        status: [],
        featured: [],
        parentId: [],
    };
    searchTerm.value = '';
    // Apply reset filters
    router.get(CategoryController.index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showFilterDrawer.value = false;
        }
    });
};

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Categories',
        href: CategoryController.index.url(),
    },
]);

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const year = String(date.getFullYear()).slice(-2);
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${month}/${day}/${year} - ${hours}:${minutes}`;
};

const getStatusSeverity = (isActive) => {
    return isActive ? 'success' : 'secondary';
};

const getStatusLabel = (isActive) => {
    return isActive ? 'Active' : 'Inactive';
};

const getFeaturedSeverity = (isFeatured) => {
    return isFeatured ? 'warning' : 'secondary';
};

const handleView = (categoryId) => {
    router.visit(CategoryController.show.url(categoryId));
};

const handleEdit = (categoryId) => {
    router.visit(CategoryController.edit.url(categoryId));
};

const handleDelete = (categoryId, categoryName) => {
    deleteCategoryId.value = categoryId;
    deleteCategoryName.value = categoryName;
    deleteWarningMessage.value = 'All subcategories will also be deleted. This action cannot be undone.';
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteCategoryId.value) return;

    deleteLoading.value = true;

    router.delete(CategoryController.destroy.url(deleteCategoryId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteCategoryId.value = null;
            deleteCategoryName.value = '';
            deleteWarningMessage.value = '';
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
    deleteCategoryId.value = null;
    deleteCategoryName.value = '';
    deleteWarningMessage.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteCategoryName.value) {
        return `Are you sure you want to delete "${deleteCategoryName.value}"?`;
    }
    return 'Are you sure you want to delete this category?';
});

const handleCreate = () => {
    router.visit(CategoryController.create.url());
};

const handleToggleStatus = (categoryId) => {
    router.post(CategoryController.toggleStatus.url(categoryId), {}, {
        preserveScroll: true,
    });
};

const handleToggleFeatured = (categoryId) => {
    router.post(CategoryController.toggleFeatured.url(categoryId), {}, {
        preserveScroll: true,
    });
};

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedCategories.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedCategories.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedCategories.value.map(cat => cat.id);

    router.post(CategoryController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedCategories.value = [];
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
    const count = selectedCategories.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'category' : 'categories'}?`;
});

const bulkDeleteWarningMessage = computed(() => {
    return 'All subcategories will also be deleted. This action cannot be undone.';
});

const hasSelectedCategories = computed(() => {
    return selectedCategories.value && selectedCategories.value.length > 0;
});
</script>

<template>

    <Head title="Categories" />

    <AppSidebarLayout title="Categories" :breadcrumbs="breadcrumbItems">
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
                        v-if="hasSelectedCategories"
                        label="Delete Selected" 
                        icon="pi pi-trash" 
                        severity="danger" 
                        outlined
                        class="flex-shrink-0"
                        @click="handleBulkDelete"
                    />
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
                    <Button label="Add Category" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Categories Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredCategories.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No categories found</p>
                    </div>

                    <DataTable v-else :value="filteredCategories" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedCategories"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}" class="p-datatable-sm"
                        stripedRows dataKey="id" :globalFilterFields="['name', 'description']"
                        @page="onPage">
                        <Column selectionMode="multiple" headerStyle="width: 3rem" />

                        <Column header="#" style="width: 60px">
                            <template #body="slotProps">
                                <span class="text-sm text-gray-600">{{ getRowNumber(slotProps.index) }}</span>
                            </template>
                        </Column>

                        <Column header="Category" :sortable="true" sortField="name">
                            <template #body="slotProps">
                                <div class="flex items-center gap-3">
                                    <div v-if="slotProps.data.image"
                                        class="h-10 w-10 rounded overflow-hidden bg-gray-100 flex-shrink-0">
                                        <img :src="slotProps.data.image_url || `/storage/${slotProps.data.image}`"
                                            :alt="slotProps.data.name" class="h-full w-full object-cover" />
                                    </div>
                                    <div v-else
                                        class="flex h-10 w-10 items-center justify-center rounded bg-blue-50 text-blue-600 flex-shrink-0">
                                        <i class="pi pi-folder text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 text-sm">{{ slotProps.data.name }}</p>
                                        <p class="text-xs text-gray-500 truncate max-w-xs">
                                            {{ slotProps.data.full_path || slotProps.data.name }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column field="slug" header="Slug" :sortable="true">
                            <template #body="slotProps">
                                <code
                                    class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">{{ slotProps.data.slug }}</code>
                            </template>
                        </Column>

                        <Column header="Parent" :sortable="true" sortField="parent_id">
                            <template #body="slotProps">
                                <span v-if="slotProps.data.parent" class="text-sm text-gray-900">
                                    {{ slotProps.data.parent.name }}
                                </span>
                                <span v-else class="text-sm text-gray-400">Root</span>
                            </template>
                        </Column>

                        <!-- <Column header="Children" style="width: 100px">
                            <template #body="slotProps">
                                <Tag v-if="slotProps.data.children && slotProps.data.children.length > 0"
                                    :value="`${slotProps.data.children.length} subcategories`" severity="info" />
                                <span v-else class="text-sm text-gray-400">None</span>
                            </template>
                        </Column> -->

                        <Column header="Status" :sortable="true" sortField="is_active" style="width: 120px">
                            <template #body="slotProps">
                                <div class="table-status-active"
                                    :class="slotProps.data.is_active ? 'status-green' : 'status-amber'">
                                    <InputSwitch :modelValue="slotProps.data.is_active"
                                        @update:modelValue="handleToggleStatus(slotProps.data.id)" />
                                </div>
                            </template>
                        </Column>

                        <Column header="Featured" :sortable="true" sortField="is_featured" style="width: 100px">
                            <template #body="slotProps">
                                <div class="flex items-center gap-2">
                                    <InputSwitch :modelValue="slotProps.data.is_featured"
                                        @update:modelValue="handleToggleFeatured(slotProps.data.id)" />
                                </div>
                            </template>
                        </Column>

                        <!-- <Column field="sort_order" header="Order" :sortable="true" style="width: 80px">
                            <template #body="slotProps">
                                <span class="text-sm font-mono text-gray-900">{{ slotProps.data.sort_order }}</span>
                            </template>
                        </Column> -->

                        <!-- <Column field="created_at" header="Created" :sortable="true" style="width: 150px">
                            <template #body="slotProps">
                                <span class="text-sm text-gray-600">{{ formatDate(slotProps.data.created_at) }}</span>
                            </template>
                        </Column> -->

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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Category" :message="deleteMessage"
            :warning-message="deleteWarningMessage" :loading="deleteLoading" @confirm="confirmDelete"
            @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal 
            v-model:visible="showBulkDeleteModal" 
            title="Delete Categories" 
            :message="bulkDeleteMessage"
            :warning-message="bulkDeleteWarningMessage" 
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
                        v-model="filters.status" 
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
                        v-model="filters.featured" 
                        :options="featuredOptions" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Featured"
                        class="w-full"
                        display="chip"
                        :showClear="true"
                    />
                </div>

                <!-- Parent Category Filter -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Parent Category</label>
                    <MultiSelect 
                        v-model="filters.parentId" 
                        :options="parentCategoryOptions.filter(opt => opt.value !== null)" 
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select Parent Categories"
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
