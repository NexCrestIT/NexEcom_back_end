<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import { computed } from 'vue';
import { route } from '@/composables/useRouter';

const props = defineProps({
    roles: {
        type: [Array, Object],
        default: () => []
    },
    permissions: {
        type: Object,
        default: () => ({})
    }
});

// Extract data array from paginated response or use array directly
const rolesList = computed(() => {
    if (Array.isArray(props.roles)) {
        return props.roles;
    }
    if (props.roles && props.roles.data) {
        return props.roles.data;
    }
    return [];
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Roles',
        href: route('admin.roles.index'),
    },
]);

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getPermissionsCount = (role) => {
    return role.permissions ? role.permissions.length : 0;
};

const handleEdit = (roleId) => {
    router.visit(route('admin.roles.edit', roleId));
};

const handleDelete = (roleId) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('admin.roles.destroy', roleId), {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by toast
            }
        });
    }
};

const handleCreate = () => {
    router.visit(route('admin.roles.create'));
};

const handleShow = (roleId) => {
    router.visit(route('admin.roles.show', roleId));
};
</script>

<template>
    <Head title="Roles" />

    <AppSidebarLayout title="Roles" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Roles List</h2>
                        <Button 
                            label="Add Role" 
                            icon="pi pi-plus" 
                            severity="success"
                            @click="handleCreate"
                        />
                    </div>
                    
                    <div v-if="rolesList.length === 0" class="text-center py-8 text-muted-foreground">
                        No roles found
                    </div>

                    <DataTable 
                        v-else
                        :value="rolesList" 
                        :paginator="true" 
                        :rows="10"
                        :rowsPerPageOptions="[5, 10, 20, 50]"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}"
                        class="p-datatable-sm"
                        stripedRows
                        dataKey="id"
                    >
                        <Column field="id" header="ID" :sortable="true" style="width: 80px" />
                        
                        <Column field="name" header="Role Name" :sortable="true" />

                        <Column field="guard_name" header="Guard" :sortable="true" />

                        <Column header="Permissions" :sortable="true" sortField="permissions">
                            <template #body="slotProps">
                                <Tag 
                                    :value="`${getPermissionsCount(slotProps.data)} Permissions`" 
                                    severity="info"
                                />
                            </template>
                        </Column>

                        <Column field="created_at" header="Created At" :sortable="true">
                            <template #body="slotProps">
                                {{ formatDate(slotProps.data.created_at) }}
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 200px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button 
                                        icon="pi pi-eye" 
                                        severity="info"
                                        size="small"
                                        outlined
                                        v-tooltip.top="'View'"
                                        @click="handleShow(slotProps.data.id)"
                                    />
                                    <Button 
                                        icon="pi pi-pencil" 
                                        severity="warning"
                                        size="small"
                                        outlined
                                        v-tooltip.top="'Edit'"
                                        @click="handleEdit(slotProps.data.id)"
                                    />
                                    <Button 
                                        icon="pi pi-trash" 
                                        severity="danger"
                                        size="small"
                                        outlined
                                        v-tooltip.top="'Delete'"
                                        @click="handleDelete(slotProps.data.id)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

