<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import { computed } from 'vue';
import { route } from '@/composables/useRouter';
import CustomerController from '@/actions/App/Http/Controllers/Admin/Customer/CustomerController.ts';

const props = defineProps({
    customers: {
        type: [Array, Object],
        default: () => []
    },
    statistics: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

// Extract data array from paginated response or use array directly
const customersList = computed(() => {
    if (Array.isArray(props.customers)) {
        return props.customers;
    }
    if (props.customers && props.customers.data) {
        return props.customers.data;
    }
    return [];
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Customers',
        href: CustomerController.index.url(),
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

const getStatusSeverity = (isActive) => {
    return isActive ? 'success' : 'danger';
};

const getStatusLabel = (isActive) => {
    return isActive ? 'Active' : 'Inactive';
};

const getVerifiedSeverity = (isVerified) => {
    return isVerified ? 'success' : 'warning';
};

const getVerifiedLabel = (isVerified) => {
    return isVerified ? 'Verified' : 'Not Verified';
};

const handleView = (customerId) => {
    router.visit(CustomerController.show.url(customerId));
};

const handleDelete = (customerId) => {
    if (confirm('Are you sure you want to delete this customer?')) {
        router.delete(CustomerController.destroy.url(customerId), {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by toast
            }
        });
    }
};

const handleToggleStatus = (customerId) => {
    router.post(CustomerController.toggleStatus.url(customerId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by toast
        }
    });
};
</script>

<template>
    <Head title="Customers" />

    <AppSidebarLayout title="Customers" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Total Customers</div>
                    <div class="text-2xl font-bold">{{ statistics.total || 0 }}</div>
                </div>
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="text-2xl font-bold text-green-600">{{ statistics.active || 0 }}</div>
                </div>
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Inactive</div>
                    <div class="text-2xl font-bold text-red-600">{{ statistics.inactive || 0 }}</div>
                </div>
                <div class="rounded-lg border border-border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Verified</div>
                    <div class="text-2xl font-bold text-blue-600">{{ statistics.verified || 0 }}</div>
                </div>
            </div>

            <div class="rounded-lg border border-border bg-card">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Customers List</h2>
                    </div>
                    
                    <div v-if="customersList.length === 0" class="text-center py-8 text-muted-foreground">
                        No customers found
                    </div>

                    <DataTable 
                        v-else
                        :value="customersList" 
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
                        
                        <Column header="Customer" :sortable="true" sortField="first_name">
                            <template #body="slotProps">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-primary-foreground font-semibold">
                                        {{ (slotProps.data.first_name || slotProps.data.name || 'C')[0]?.toUpperCase() || 'C' }}
                                    </div>
                                    <div>
                                        <p class="font-medium">
                                            {{ slotProps.data.first_name || slotProps.data.last_name ? `${slotProps.data.first_name || ''} ${slotProps.data.last_name || ''}`.trim() : slotProps.data.name }}
                                        </p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ slotProps.data.email || slotProps.data.phone_number || 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column field="first_name" header="First Name" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.first_name || 'N/A' }}
                            </template>
                        </Column>

                        <Column field="last_name" header="Last Name" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.last_name || 'N/A' }}
                            </template>
                        </Column>

                        <Column field="email" header="Email" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.email || 'N/A' }}
                            </template>
                        </Column>

                        <Column field="phone_number" header="Phone" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.phone_number || 'N/A' }}
                            </template>
                        </Column>

                        <Column field="city" header="City" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.city || 'N/A' }}
                            </template>
                        </Column>

                        <Column field="state" header="State / County" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.state || 'N/A' }}
                            </template>
                        </Column>

                        <Column field="postcode" header="Postcode" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.postcode || 'N/A' }}
                            </template>
                        </Column>

                        <Column field="country" header="Country" :sortable="true">
                            <template #body="slotProps">
                                {{ slotProps.data.country || 'N/A' }}
                            </template>
                        </Column>

                        <Column header="Status" :sortable="true" sortField="is_active">
                            <template #body="slotProps">
                                <Tag 
                                    :value="getStatusLabel(slotProps.data.is_active)" 
                                    :severity="getStatusSeverity(slotProps.data.is_active)"
                                />
                            </template>
                        </Column>

                        <Column header="Verified" :sortable="true" sortField="is_verified">
                            <template #body="slotProps">
                                <Tag 
                                    :value="getVerifiedLabel(slotProps.data.is_verified)" 
                                    :severity="getVerifiedSeverity(slotProps.data.is_verified)"
                                />
                            </template>
                        </Column>

                        <Column field="created_at" header="Registered" :sortable="true">
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
                                        @click="handleView(slotProps.data.id)"
                                    />
                                    <Button 
                                        :icon="slotProps.data.is_active ? 'pi pi-ban' : 'pi pi-check'" 
                                        :severity="slotProps.data.is_active ? 'warning' : 'success'"
                                        size="small"
                                        outlined
                                        @click="handleToggleStatus(slotProps.data.id)"
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

