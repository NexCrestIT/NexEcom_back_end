<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { computed, ref } from 'vue';
import LabelController from '@/actions/App/Http/Controllers/Admin/Label/LabelController';
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';

const props = defineProps({
    labels: {
        type: Array,
        default: () => []
    }
});

const searchTerm = ref('');
const selectedLabels = ref([]);
const currentPage = ref(0);
const rowsPerPage = ref(10);

// Delete confirmation modal state
const showDeleteModal = ref(false);
const deleteLoading = ref(false);
const deleteLabelId = ref(null);
const deleteLabelName = ref('');

// Bulk delete state
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);

// Filter categories based on search
const filteredLabels = computed(() => {
    if (!searchTerm.value) {
        return props.labels;
    }
    const term = searchTerm.value.toLowerCase();
    return props.labels.filter(label =>
        label.name.toLowerCase().includes(term)
    );
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Labels',
        href: LabelController.index.url(),
    },
]);

const getRowNumber = (rowIndex) => {
    return (currentPage.value * rowsPerPage.value) + rowIndex + 1;
};

const onPage = (event) => {
    currentPage.value = event.page;
    rowsPerPage.value = event.rows;
};

const handleView = (labelId) => {
    router.visit(LabelController.show.url(labelId));
};

const handleEdit = (labelId) => {
    router.visit(LabelController.edit.url(labelId));
};

const handleDelete = (labelId, labelName) => {
    deleteLabelId.value = labelId;
    deleteLabelName.value = labelName;
    showDeleteModal.value = true;
    deleteLoading.value = false;
};

const confirmDelete = () => {
    if (!deleteLabelId.value) return;

    deleteLoading.value = true;

    router.delete(LabelController.destroy.url(deleteLabelId.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteLoading.value = false;
            showDeleteModal.value = false;
            deleteLabelId.value = null;
            deleteLabelName.value = '';
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
    deleteLabelId.value = null;
    deleteLabelName.value = '';
    deleteLoading.value = false;
};

const deleteMessage = computed(() => {
    if (deleteLabelName.value) {
        return `Are you sure you want to delete "${deleteLabelName.value}"?`;
    }
    return 'Are you sure you want to delete this label?';
});

const handleCreate = () => {
    router.visit(LabelController.create.url());
};

// Bulk delete handlers
const handleBulkDelete = () => {
    if (selectedLabels.value.length === 0) return;
    showBulkDeleteModal.value = true;
    bulkDeleteLoading.value = false;
};

const confirmBulkDelete = () => {
    if (selectedLabels.value.length === 0) return;

    bulkDeleteLoading.value = true;
    const ids = selectedLabels.value.map(label => label.id);

    router.post(LabelController.bulkDelete.url(), { ids }, {
        preserveScroll: true,
        onSuccess: () => {
            bulkDeleteLoading.value = false;
            showBulkDeleteModal.value = false;
            selectedLabels.value = [];
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
    const count = selectedLabels.value.length;
    if (count === 0) return '';
    return `Are you sure you want to delete ${count} ${count === 1 ? 'label' : 'labels'}?`;
});

const hasSelectedLabels = computed(() => {
    return selectedLabels.value && selectedLabels.value.length > 0;
});
</script>

<template>

    <Head title="Labels" />

    <AppSidebarLayout title="Labels" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Search and Actions -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="w-full sm:w-auto search-input-wrapper">
                    <i class="pi pi-search search-icon" />
                    <InputText v-model="searchTerm" placeholder="Search" class="w-full" />
                </div>
                <div class="flex gap-2">
                    <div class="relative inline-block">
                        <Button v-if="hasSelectedLabels" label="Delete Selected" icon="pi pi-trash" severity="danger"
                            outlined class="flex-shrink-0" @click="handleBulkDelete" />
                        <span v-if="hasSelectedLabels"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-semibold rounded-full h-5 w-5 flex items-center justify-center shadow-lg border-2 border-white"
                            style="min-width: 1.25rem;">
                            {{ selectedLabels.length }}
                        </span>
                    </div>
                    <Button label="Add Label" icon="pi pi-plus" severity="primary" @click="handleCreate"
                        class="flex-shrink-0" />
                </div>
            </div>

            <!-- Labels Table -->
            <div class="data-table-container">
                <div class="overflow-x-auto">
                    <div v-if="filteredLabels.length === 0" class="text-center py-12">
                        <i class="pi pi-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500">No labels found</p>
                    </div>

                    <DataTable v-else :value="filteredLabels" :paginator="true" :rows="rowsPerPage"
                        :rowsPerPageOptions="[5, 10, 20, 50]" v-model:selection="selectedLabels"
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

                        <Column field="name" header="Label Name" :sortable="true">
                            <template #body="slotProps">
                                <div class="flex items-center h-full">
                                    <span class="font-medium text-gray-900">{{ slotProps.data.name }}</span>
                                </div>
                            </template>
                        </Column>

                        <!-- <Column header="Created" :sortable="true" sortField="created_at" style="width: 180px">
                            <template #body="slotProps">
                                <span class="text-sm text-gray-600">
                                    {{ new Date(slotProps.data.created_at).toLocaleDateString() }}
                                </span>
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
        <DeleteConfirmationModal v-model:visible="showDeleteModal" title="Delete Label" :message="deleteMessage"
            :loading="deleteLoading" @confirm="confirmDelete" @cancel="cancelDelete" />

        <!-- Bulk Delete Confirmation Modal -->
        <DeleteConfirmationModal v-model:visible="showBulkDeleteModal" title="Delete Labels"
            :message="bulkDeleteMessage" :loading="bulkDeleteLoading" @confirm="confirmBulkDelete"
            @cancel="cancelBulkDelete" />
    </AppSidebarLayout>
</template>
