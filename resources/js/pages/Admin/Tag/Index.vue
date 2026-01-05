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
import TagController from '@/actions/App/Http/Controllers/Admin/Tag/TagController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    tags: {
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
const selectedTags = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Filter drawer state
const showFilterDrawer = ref(false);

// Filter state - initialize from props
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
const deleteTagId = ref(null);
const deleteTagName = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

// Tags are already filtered on the backend
const filteredTags = computed(() => {
    return props.tags;
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Tags',
        href: TagController.index.url(),
    },
]);

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleView = (tagId) => {
    router.visit(TagController.show.url(tagId));
};

const handleEdit = (tagId) => {
    router.visit(TagController.edit.url(tagId));
};

const handleDelete = (tagId, tagName) => {
    deleteTagId.value = tagId;
    deleteTagName.value = tagName;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteTagId.value) return;

    deleteLoading.value = true;

    router.delete(TagController.destroy.url(deleteTagId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteTagId.value = null;
            deleteTagName.value = '';
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
    deleteTagId.value = null;
    deleteTagName.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteTagName.value) {
        return `Are you sure you want to delete "${deleteTagName.value}"?`;
    }
    return 'Are you sure you want to delete this tag?';
});

const handleCreate = () => {
    router.visit(TagController.create.url());
};

const handleToggleStatus = (tagId) => {
    router.post(TagController.toggleStatus.url(tagId), {}, {
        preserveScroll: true,
    });
};

// Count active filters
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.isActive && filters.value.isActive.length > 0) count++;
    return count;
});

// Apply filters - send to backend
const applyFilters = (closeDrawer = true) => {
    const params = {};
    
    if (filters.value.isActive && filters.value.isActive.length > 0) {
        params.is_active = filters.value.isActive;
    }
    if (searchTerm.value) {
        params.search = searchTerm.value;
    }

    router.get(TagController.index.url(), params, {
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
    };
    searchTerm.value = '';
    router.get(TagController.index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showFilterDrawer.value = false;
        }
    });
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedTags.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedTags.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedTags.value.map(tag => tag.id);

    router.post(TagController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedTags.value = [];
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
    const count = selectedTags.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'tag' : 'tags'}?`;
});

const hasSelectedTags = computed(() => {
    return selectedTags.value && selectedTags.value.length > 0;
});
</script>

<template>
    <Head title="Tags" />

    <AppSidebarLayout title="Tags" :breadcrumbs="breadcrumbItems">
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
                            v-if="hasSelectedTags"
                            label="Delete Selected" 
                            icon="pi pi-trash" 
                            severity="danger" 
                            outlined
                            class="flex-shrink-0"
                            @click="handleBulkDelete"
                        />
                        <span 
                            v-if="hasSelectedTags" 
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;"
                        >
                            {{ selectedTags.length }}
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
                    <Button label="Add Tag" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Tags Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredTags.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No tags found</p>
                    </div>

                    <DataTable v-else :value="filteredTags" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedTags"
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

                        <Column field="name" header="Tag Name" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex items-center h-full gap-2">
                                    <span 
                                        v-if="slotProps.data.color" 
                                        class="w-4 h-4 rounded border"
                                        :style="{ backgroundColor: slotProps.data.color }"
                                        :title="slotProps.data.color"
                                    ></span>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ slotProps.data.name }}</span>
                                        <span v-if="slotProps.data.description" class="text-xs text-gray-500 truncate max-w-xs">
                                            {{ slotProps.data.description }}
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column field="slug" header="Slug" :sortable="true" style="width: 200px">
                            <template #body="slotProps">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ slotProps.data.slug }}</code>
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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Tag" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal 
            v-model:visible="showBulkDeleteModal" 
            title="Delete Tags" 
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

