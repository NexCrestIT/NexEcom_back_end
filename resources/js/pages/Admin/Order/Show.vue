<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import { computed, ref } from 'vue';
import { route } from '@/composables/useRouter';
import OrderController from '@/actions/App/Http/Controllers/Admin/Order/OrderController.ts';

const props = defineProps({
    order: {
        type: Object,
        required: true
    },
    orderStatuses: {
        type: Object,
        default: () => ({})
    },
    paymentStatuses: {
        type: Object,
        default: () => ({})
    },
    nextPossibleStatuses: {
        type: Object,
        default: () => ({})
    },
    canCancel: {
        type: Boolean,
        default: false
    },
    canRefund: {
        type: Boolean,
        default: false
    },
    canShip: {
        type: Boolean,
        default: false
    }
});

const newStatus = ref('');
const newPaymentStatus = ref('');
const notes = ref(props.order.notes || '');
const refundAmount = ref(props.order.total_amount);
const refundReason = ref('');
const isUpdatingStatus = ref(false);
const isUpdatingPaymentStatus = ref(false);
const isUpdatingNotes = ref(false);
const isProcessingRefund = ref(false);

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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusSeverity = (status) => {
    const severityMap = {
        pending: 'warning',
        completed: 'success',
        cancelled: 'danger',
        processing: 'info',
        shipped: 'primary',
        delivered: 'success',
        returned: 'secondary',
    };
    return severityMap[status] || 'secondary';
};

const getPaymentStatusSeverity = (status) => {
    const severityMap = {
        pending: 'warning',
        paid: 'success',
        failed: 'danger',
        refunded: 'secondary',
    };
    return severityMap[status] || 'secondary';
};

const updateStatus = () => {
    if (!newStatus.value) return;

    isUpdatingStatus.value = true;
    router.post(
        OrderController.updateStatus.url(props.order.id),
        {
            status: newStatus.value,
            notes: notes.value,
        },
        {
            onFinish: () => {
                isUpdatingStatus.value = false;
                newStatus.value = '';
            },
        }
    );
};

const updatePaymentStatus = () => {
    if (!newPaymentStatus.value) return;

    isUpdatingPaymentStatus.value = true;
    router.post(
        OrderController.updatePaymentStatus.url(props.order.id),
        {
            payment_status: newPaymentStatus.value,
        },
        {
            onFinish: () => {
                isUpdatingPaymentStatus.value = false;
                newPaymentStatus.value = '';
            },
        }
    );
};

const updateNotes = () => {
    isUpdatingNotes.value = true;
    router.post(
        OrderController.updateNotes.url(props.order.id),
        {
            notes: notes.value,
        },
        {
            onFinish: () => {
                isUpdatingNotes.value = false;
            },
        }
    );
};

const processRefund = () => {
    if (!refundAmount.value) return;

    isProcessingRefund.value = true;
    router.post(
        OrderController.processRefund.url(props.order.id),
        {
            amount: refundAmount.value,
            reason: refundReason.value,
        },
        {
            onFinish: () => {
                isProcessingRefund.value = false;
                refundAmount.value = props.order.total_amount;
                refundReason.value = '';
            },
        }
    );
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
    {
        title: props.order.order_number,
        href: OrderController.show.url(props.order.id),
    },
]);
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />
    <AppSidebarLayout :title="`Order ${order.order_number}`" :breadcrumbs="breadcrumbItems">
        <!-- Top summary -->
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Order Number</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ order.order_number }}</div>
                </div>
                <div class="flex items-center gap-3">
                    <Tag :value="orderStatuses[order.status] || order.status" :severity="getStatusSeverity(order.status)" />
                    <Tag :value="paymentStatuses[order.payment_status] || order.payment_status" :severity="getPaymentStatusSeverity(order.payment_status)" />
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(order.total_amount) }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Status Card -->
                <Card>
                    <template #header>
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 text-blue-900 font-semibold border-b border-blue-200">
                            Order Status Management
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Status</div>
                                <Tag
                                    :value="orderStatuses[order.status] || order.status"
                                    :severity="getStatusSeverity(order.status)"
                                    class="mt-2"
                                />
                            </div>
                        </div>

                        <div v-if="Object.keys(nextPossibleStatuses).length > 0" class="border-t pt-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Change Status To
                            </label>
                            <Dropdown
                                v-model="newStatus"
                                :options="Object.entries(nextPossibleStatuses).map(([key, value]) => ({ label: value, value: key }))"
                                option-label="label"
                                option-value="value"
                                placeholder="Select new status"
                                class="w-full mb-3"
                            />
                            <Button
                                label="Update Order Status"
                                icon="pi pi-check"
                                :disabled="!newStatus || isUpdatingStatus"
                                :loading="isUpdatingStatus"
                                @click="updateStatus"
                                class="w-full"
                            />
                        </div>

                        <div v-if="!Object.keys(nextPossibleStatuses).length" class="border-t pt-4 text-sm text-gray-500 dark:text-gray-400">
                            No status transitions available for this order
                        </div>
                    </div>
                    </template>
                </Card>

                <!-- Payment Status Card -->
                <Card>
                    <template #header>
                        <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 text-green-900 font-semibold border-b border-green-200">
                            Payment Information
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Method</div>
                                <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                                    {{ order.payment_method || 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Status</div>
                                <Tag
                                    :value="paymentStatuses[order.payment_status] || order.payment_status"
                                    :severity="getPaymentStatusSeverity(order.payment_status)"
                                    class="mt-1"
                                />
                            </div>
                            <div v-if="order.payment_method==='razorpay' && order.payment_status==='pending' && order.razorpay_order_id" class="col-span-2">
                                <a :href="`https://dashboard.razorpay.com/app/paymentlinks/${order.razorpay_order_id}`" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                                    <i class="pi pi-external-link" />
                                    Open Payment Link in Razorpay
                                </a>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Update Order Status
                            </label>
                            <Dropdown
                                v-model="newPaymentStatus"
                                :options="Object.entries(paymentStatuses).map(([key, value]) => ({ label: value, value: key }))"
                                option-label="label"
                                option-value="value"
                                placeholder="Select payment status"
                                class="w-full mb-3"
                            />
                            <Button
                                label="Update Order Status"
                                icon="pi pi-check"
                                :disabled="!newPaymentStatus || isUpdatingPaymentStatus"
                                :loading="isUpdatingPaymentStatus"
                                @click="updatePaymentStatus"
                                class="w-full"
                            />
                        </div>
                    </div>
                    </template>
                </Card>

                <!-- Order Items -->
                <Card>
                    <template #header>
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 text-purple-900 font-semibold border-b border-purple-200">
                            Order Items
                        </div>
                    </template>
                    <template #content>
                    <DataTable :value="order.items || order.order_items" striped-rows class="p-datatable-sm">
                        <Column field="product.name" header="Product Name" style="width: 40%">
                            <template #body="slotProps">
                                {{ slotProps.data.product?.name || 'N/A' }}
                            </template>
                        </Column>
                        <Column field="quantity" header="Qty" style="width: 15%"></Column>
                        <Column field="price" header="Unit Price" style="width: 20%">
                            <template #body="slotProps">
                                {{ formatCurrency(slotProps.data.price) }}
                            </template>
                        </Column>
                        <Column header="Total" style="width: 25%">
                            <template #body="slotProps">
                                {{ formatCurrency(slotProps.data.quantity * slotProps.data.price) }}
                            </template>
                        </Column>
                    </DataTable>
                    <div class="mt-4 space-y-2 border-t pt-4 text-right">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                            Total Amount: {{ formatCurrency(order.total_amount) }}
                        </div>
                    </div>
                    </template>
                </Card>

                <!-- Notes -->
                <Card>
                    <template #header>
                        <div class="bg-gradient-to-r from-orange-50 to-orange-100 p-4 text-orange-900 font-semibold border-b border-orange-200">
                            Order Notes
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                        <Textarea
                            v-model="notes"
                            rows="4"
                            placeholder="Add internal notes..."
                            class="w-full"
                        />
                        <Button
                            label="Save Notes"
                            icon="pi pi-save"
                            :loading="isUpdatingNotes"
                            @click="updateNotes"
                            class="w-full"
                        />
                    </div>
                    </template>
                </Card>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Customer Information -->
                <Card>
                    <template #header>
                        <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 p-4 text-indigo-900 font-semibold border-b border-indigo-200">
                            Customer Information
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ order.customer?.name || 'N/A' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ order.customer?.email || 'N/A' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ order.customer?.phone_number || 'N/A' }}
                            </div>
                        </div>
                    </div>
                    </template>
                </Card>

                <!-- Shipping Address -->
                <Card v-if="order.address">
                    <template #header>
                        <div class="bg-gradient-to-r from-cyan-50 to-cyan-100 p-4 text-cyan-900 font-semibold border-b border-cyan-200">
                            Shipping Address
                        </div>
                    </template>
                    <template #content>
                    <div class="space-y-2 text-sm">
                        <div>
                            <strong>{{ order.address.first_name }} {{ order.address.last_name }}</strong>
                        </div>
                        <div>{{ order.address.address_line_1 }}</div>
                        <div v-if="order.address.address_line_2">{{ order.address.address_line_2 }}</div>
                        <div>{{ order.address.city }}, {{ order.address.state }} {{ order.address.postal_code }}</div>
                        <div>{{ order.address.country }}</div>
                    </div>
                    </template>
                </Card>

                <!-- Order Dates -->
                <Card>
                    <template #header>
                        <div class="bg-gradient-to-r from-teal-50 to-teal-100 p-4 text-teal-900 font-semibold border-b border-teal-200">
                            Order Timeline
                        </div>
                    </template>
                    <template #content>
                    <div class="space-y-3">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Ordered Date</div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ formatDateDisplay(order.created_at) }}
                            </div>
                        </div>
                        <div v-if="order.paid_at">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Paid Date</div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ formatDateDisplay(order.paid_at) }}
                            </div>
                        </div>
                    </div>
                    </template>
                </Card>

                <!-- Refund Section -->
                <Card v-if="canRefund">
                    <template #header>
                        <div class="bg-gradient-to-r from-red-50 to-red-100 p-4 text-red-900 font-semibold border-b border-red-200">
                            Process Refund
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Refund Amount
                            </label>
                            <input
                                v-model.number="refundAmount"
                                type="number"
                                step="0.01"
                                :max="order.total_amount"
                                class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                            />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Reason
                            </label>
                            <Textarea
                                v-model="refundReason"
                                rows="3"
                                placeholder="Enter refund reason..."
                                class="w-full"
                            />
                        </div>
                        <Button
                            label="Process Refund"
                            icon="pi pi-check"
                            severity="danger"
                            :loading="isProcessingRefund"
                            @click="processRefund"
                            class="w-full"
                        />
                    </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppSidebarLayout>
</template>
