<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputSwitch from 'primevue/inputswitch';
import { computed, ref } from 'vue';
import ScentFamilyController from '@/actions/App/Http/Controllers/Admin/ScentFamily/ScentFamilyController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    scentFamilies: {
        type: Array,
        default: () => []
    }
});

const searchTerm = ref('');
const selectedScentFamilies = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deleteScentFamilyId = ref(null);
const deleteScentFamilyName = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

// Filter scent families based on search
const filteredScentFamilies = computed(() => {
    if (!searchTerm.value) {
        return props.scentFamilies;
    }
    const term = searchTerm.value.toLowerCase();
    return props.scentFamilies.filter(scentFamily =>
        scentFamily.name.toLowerCase().includes(term)
    );
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Scent Families',
        href: ScentFamilyController.index.url(),
    },
]);

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleView = (scentFamilyId) => {
    router.visit(ScentFamilyController.show.url(scentFamilyId));
};

const handleEdit = (scentFamilyId) => {
    router.visit(ScentFamilyController.edit.url(scentFamilyId));
};

const handleDelete = (scentFamilyId, scentFamilyName) => {
    deleteScentFamilyId.value = scentFamilyId;
    deleteScentFamilyName.value = scentFamilyName;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteScentFamilyId.value) return;

    deleteLoading.value = true;

    router.delete(ScentFamilyController.destroy.url(deleteScentFamilyId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteScentFamilyId.value = null;
            deleteScentFamilyName.value = '';
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
    deleteScentFamilyId.value = null;
    deleteScentFamilyName.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteScentFamilyName.value) {
        return `Are you sure you want to delete "${deleteScentFamilyName.value}"?`;
    }
    return 'Are you sure you want to delete this scent family?';
});

const handleCreate = () => {
    router.visit(ScentFamilyController.create.url());
};

const handleToggleStatus = (scentFamilyId) => {
    router.post(ScentFamilyController.toggleStatus.url(scentFamilyId), {}, {
        preserveScroll: true,
    });
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedScentFamilies.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedScentFamilies.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedScentFamilies.value.map(scentFamily => scentFamily.id);

    router.post(ScentFamilyController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedScentFamilies.value = [];
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
    const count = selectedScentFamilies.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'scent family' : 'scent families'}?`;
});

const hasSelectedScentFamilies = computed(() => {
    return selectedScentFamilies.value && selectedScentFamilies.value.length > 0;
});
</script>

<template>
    <Head title="Scent Families" />

    <AppSidebarLayout title="Scent Families" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Search and Actions -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="w-full sm:w-auto search-input-wrapper">
                    <i class="pi pi-search search-icon" />
                    <InputText v-model="searchTerm" placeholder="Search" class="w-full" />
                </div>
                <div class="flex gap-2">
                    <div class="relative inline-block">
                        <Button v-if="hasSelectedScentFamilies" label="Delete Selected" icon="pi pi-trash" severity="danger"
                            outlined class="flex-shrink-0" @click="handleBulkDelete" />
                        <span v-if="hasSelectedScentFamilies"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;">
                            {{ selectedScentFamilies.length }}
                        </span>
                    </div>
                    <Button label="Add Scent Family" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Scent Families Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredScentFamilies.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No scent families found</p>
                    </div>

                    <DataTable v-else :value="filteredScentFamilies" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedScentFamilies"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}" class="p-datatable-sm"
                        stripedRows dataKey="id" :globalFilterFields="['name']" @page="onPage">
                        <Column selectionMode="multiple" headerStyle="width: 3rem" />

                        <Column header="#" style="width: 60px">
                            <template #body="slotProps">
                                <div class="flex items-center h-full">
                                    <span class="text-sm text-gray-600">{{ getRowNumber(slotProps.index) }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="name" header="Scent Family Name" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex items-center h-full">
                                    <span class="font-medium text-gray-900">{{ slotProps.data.name }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="status" header="Status" :sortable="true" sortField="status" style="width: 120px">
                            <template #body="slotProps">
                                <InputSwitch 
                                    :modelValue="slotProps.data.status" 
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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Scent Family" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal v-model:visible="showBulkDeleteModal" title="Delete Scent Families"
            :message="bulkDeleteMessage" :loading="bulkDeleteLoading" @confirm="confirmBulkDelete"
            @cancel="cancelBulkDelete" />
    </AppSidebarLayout>
</template>

