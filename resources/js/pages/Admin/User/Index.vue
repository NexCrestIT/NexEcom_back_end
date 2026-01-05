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
    users: {
        type: [Array, Object],
        default: () => []
    },
    roles: {
        type: Array,
        default: () => []
    }
});

// Extract data array from paginated response or use array directly
const usersList = computed(() => {
    if (Array.isArray(props.users)) {
        return props.users;
    }
    if (props.users && props.users.data) {
        return props.users.data;
    }
    return [];
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Users',
        href: route('admin.users.index'),
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

const getStatusSeverity = (verified) => {
    return verified ? 'success' : 'warning';
};

const getStatusLabel = (verified) => {
    return verified ? 'Verified' : 'Not Verified';
};

const handleEdit = (userId) => {
    router.visit(route('admin.users.edit', userId));
};

const handleDelete = (userId) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.destroy', userId), {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by toast
            }
        });
    }
};

const handleCreate = () => {
    router.visit(route('admin.users.create'));
};
</script>

<template>
    <Head title="Users" />

    <AppSidebarLayout title="Users" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Users List</h2>
                        <Button 
                            label="Add User" 
                            icon="pi pi-plus" 
                            severity="success"
                            @click="handleCreate"
                        />
                    </div>
                    
                    <div v-if="usersList.length === 0" class="text-center py-8 text-muted-foreground">
                        No users found
                    </div>

                    <DataTable 
                        v-else
                        :value="usersList" 
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
                        
                        <Column header="User" :sortable="true" sortField="name">
                            <template #body="slotProps">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-primary-foreground font-semibold">
                                        {{ slotProps.data.name?.charAt(0)?.toUpperCase() || 'U' }}
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ slotProps.data.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ slotProps.data.email }}</p>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column field="email" header="Email" :sortable="true" />

                        <Column header="Status" :sortable="true" sortField="email_verified_at">
                            <template #body="slotProps">
                                <Tag 
                                    :value="getStatusLabel(slotProps.data.email_verified_at)" 
                                    :severity="getStatusSeverity(slotProps.data.email_verified_at)"
                                />
                            </template>
                        </Column>

                        <Column field="created_at" header="Created At" :sortable="true">
                            <template #body="slotProps">
                                {{ formatDate(slotProps.data.created_at) }}
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 150px">
                            <template #body="slotProps">
                                <div class="flex gap-2">
                                    <Button 
                                        icon="pi pi-pencil" 
                                        severity="info"
                                        size="small"
                                        outlined
                                        @click="handleEdit(slotProps.data.id)"
                                    />
                                    <Button 
                                        icon="pi pi-trash" 
                                        severity="danger"
                                        size="small"
                                        outlined
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
