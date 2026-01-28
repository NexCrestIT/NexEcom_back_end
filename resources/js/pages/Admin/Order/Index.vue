<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import { computed, ref } from 'vue';
import { route } from '@/composables/useRouter';
import OrderController from '@/actions/App/Http/Controllers/Admin/Order/OrderController.ts';

const props = defineProps({
    orders: {
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
    },
    orderStatuses: {
        type: Object,
        default: () => ({})
    },
    paymentStatuses: {
        type: Object,
        default: () => ({})
    }
});

// Extract paginated data
const ordersList = computed(() => {
    if (Array.isArray(props.orders)) {
        return props.orders;
    }
    if (props.orders && props.orders.data) {
        return props.orders.data;
    }
    return [];
});

// Form filters
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const paymentStatusFilter = ref(props.filters.payment_status || '');
const fromDate = ref(props.filters.from_date || null);
const toDate = ref(props.filters.to_date || null);
const minAmount = ref(props.filters.min_amount || null);
const maxAmount = ref(props.filters.max_amount || null);

// Apply filters
const applyFilters = () => {
    const params = {
        search: searchQuery.value || undefined,
        status: statusFilter.value || undefined,
        payment_status: paymentStatusFilter.value || undefined,
        from_date: fromDate.value ? formatDate(fromDate.value) : undefined,
        to_date: toDate.value ? formatDate(toDate.value) : undefined,
        min_amount: minAmount.value || undefined,
        max_amount: maxAmount.value || undefined,
    };

    router.get(OrderController.index.url(), params, {
        preserveScroll: true,
    });
};

const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    paymentStatusFilter.value = '';
    fromDate.value = null;
    toDate.value = null;
    minAmount.value = null;
    maxAmount.value = null;

    router.get(OrderController.index.url());
};

const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    return d.toISOString().split('T')[0];
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
    }).format(value);
};

const formatDateDisplay = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusSeverity = (status) => {
    const severityMap = {
        pending: 'warning',
        processing: 'info',
        shipped: 'primary',
        delivered: 'success',
        cancelled: 'danger',
        returned: 'secondary',
    };
    return severityMap[status] || 'secondary';
};

const getPaymentStatusSeverity = (status) => {
    const severityMap = {
        pending: 'warning',
        completed: 'success',
        failed: 'danger',
        refunded: 'secondary',
    };
    return severityMap[status] || 'secondary';
};

const viewOrder = (orderId) => {
    router.visit(OrderController.show.url(orderId));
};

const exportCSV = () => {
    const params = {
        search: searchQuery.value || undefined,
        status: statusFilter.value || undefined,
        payment_status: paymentStatusFilter.value || undefined,
    };
    window.location.href = OrderController.export.url() + '?' + new URLSearchParams(params).toString();
};

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Orders',
        href: OrderController.index.url(),
    },
]);
</script>

<template>
    <Head title="Orders" />
    <AppSidebarLayout title="Orders" :breadcrumbs="breadcrumbItems">
        <!-- Statistics Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</div>
                <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ statistics.total_orders || 0 }}
                </div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Orders</div>
                <div class="mt-2 text-2xl font-bold text-yellow-600">
                    {{ statistics.pending_orders || 0 }}
                </div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</div>
                <div class="mt-2 text-2xl font-bold text-green-600">
                    {{ formatCurrency(statistics.total_revenue || 0) }}
                </div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivered Orders</div>
                <div class="mt-2 text-2xl font-bold text-blue-600">
                    {{ statistics.delivered_orders || 0 }}
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Search -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Search Order
                    </label>
                    <InputText
                        v-model="searchQuery"
                        placeholder="Order number or customer name..."
                        class="w-full"
                        @keyup.enter="applyFilters"
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Order Status
                    </label>
                    <Dropdown
                        v-model="statusFilter"
                        :options="Object.entries(orderStatuses).map(([key, value]) => ({ label: value, value: key }))"
                        option-label="label"
                        option-value="value"
                        placeholder="All Statuses"
                        class="w-full"
                    />
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Payment Status
                    </label>
                    <Dropdown
                        v-model="paymentStatusFilter"
                        :options="Object.entries(paymentStatuses).map(([key, value]) => ({ label: value, value: key }))"
                        option-label="label"
                        option-value="value"
                        placeholder="All Payments"
                        class="w-full"
                    />
                </div>

                <!-- From Date -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        From Date
                    </label>
                    <Calendar
                        v-model="fromDate"
                        date-format="yy-mm-dd"
                        placeholder="Select from date"
                        class="w-full"
                    />
                </div>

                <!-- To Date -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        To Date
                    </label>
                    <Calendar
                        v-model="toDate"
                        date-format="yy-mm-dd"
                        placeholder="Select to date"
                        class="w-full"
                    />
                </div>

                <!-- Min Amount -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Min Amount
                    </label>
                    <InputText
                        v-model.number="minAmount"
                        placeholder="Minimum amount"
                        type="number"
                        class="w-full"
                    />
                </div>

                <!-- Max Amount -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Max Amount
                    </label>
                    <InputText
                        v-model.number="maxAmount"
                        placeholder="Maximum amount"
                        type="number"
                        class="w-full"
                    />
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="mt-4 flex flex-col gap-2 md:flex-row">
                <Button
                    label="Apply Filters"
                    icon="pi pi-search"
                    @click="applyFilters"
                    class="flex-1"
                />
                <Button
                    label="Reset Filters"
                    icon="pi pi-times"
                    severity="secondary"
                    @click="resetFilters"
                    class="flex-1"
                />
                <Button
                    label="Export CSV"
                    icon="pi pi-download"
                    severity="info"
                    @click="exportCSV"
                    class="flex-1"
                />
            </div>
        </div>

        <!-- Orders Table -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <DataTable
                :value="ordersList"
                paginator
                :rows="15"
                :total-records="props.orders?.meta?.total || 0"
                responsive-layout="scroll"
                striped-rows
                class="p-datatable-sm"
            >
                <Column field="id" header="ID" style="width: 10%"></Column>
                <Column field="order_number" header="Order Number" style="width: 15%">
                    <template #body="slotProps">
                        <Button
                            :label="slotProps.data.order_number"
                            link
                            @click="viewOrder(slotProps.data.id)"
                        />
                    </template>
                </Column>
                <Column field="customer.name" header="Customer" style="width: 20%">
                    <template #body="slotProps">
                        {{ slotProps.data.customer?.name || 'N/A' }}
                    </template>
                </Column>
                <Column field="total_amount" header="Amount" style="width: 12%">
                    <template #body="slotProps">
                        {{ formatCurrency(slotProps.data.total_amount) }}
                    </template>
                </Column>
                <Column field="status" header="Status" style="width: 12%">
                    <template #body="slotProps">
                        <Tag
                            :value="orderStatuses[slotProps.data.status] || slotProps.data.status"
                            :severity="getStatusSeverity(slotProps.data.status)"
                        />
                    </template>
                </Column>
                <Column field="payment_status" header="Payment" style="width: 12%">
                    <template #body="slotProps">
                        <Tag
                            :value="paymentStatuses[slotProps.data.payment_status] || slotProps.data.payment_status"
                            :severity="getPaymentStatusSeverity(slotProps.data.payment_status)"
                        />
                    </template>
                </Column>
                <Column field="created_at" header="Date" style="width: 12%">
                    <template #body="slotProps">
                        {{ formatDateDisplay(slotProps.data.created_at) }}
                    </template>
                </Column>
                <Column header="Action" style="width: 7%">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-eye"
                            rounded
                            text
                            severity="info"
                            @click="viewOrder(slotProps.data.id)"
                            v-tooltip="'View'"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppSidebarLayout>
</template>
