<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import InputError from '@/components/InputError.vue';
import { computed, ref, onMounted } from 'vue';
import InventoryController from '@/actions/App/Http/Controllers/Admin/Inventory/InventoryController';

const props = defineProps({
    inventory: {
        type: Object,
        required: true
    }
});

const showAdjustStockDialog = ref(false);
const adjustForm = useForm({
    quantity: 0,
    type: 'adjustment',
    reason: '',
    notes: '',
});

const typeOptions = [
    { label: 'Stock In', value: 'in' },
    { label: 'Stock Out', value: 'out' },
    { label: 'Adjustment', value: 'adjustment' },
];

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Inventory',
        href: InventoryController.index.url(),
    },
    {
        title: props.inventory.product?.name || 'Inventory',
        href: InventoryController.show.url(props.inventory.id),
    },
]);

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStockStatus = () => {
    if (props.inventory.available_quantity <= 0) {
        return { label: 'Out of Stock', severity: 'danger' };
    } else if (props.inventory.is_low_stock) {
        return { label: 'Low Stock', severity: 'warning' };
    } else {
        return { label: 'In Stock', severity: 'success' };
    }
};

const handleEdit = () => {
    router.visit(InventoryController.edit.url(props.inventory.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this inventory item?')) {
        router.delete(InventoryController.destroy.url(props.inventory.id));
    }
};

const openAdjustStockDialog = () => {
    adjustForm.reset();
    adjustForm.quantity = 0;
    adjustForm.type = 'adjustment';
    showAdjustStockDialog.value = true;
};

const closeAdjustStockDialog = () => {
    showAdjustStockDialog.value = false;
    adjustForm.reset();
};

const submitAdjustStock = () => {
    adjustForm.post(InventoryController.adjustStock.url(props.inventory.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeAdjustStockDialog();
        }
    });
};

onMounted(() => {
    // Check if URL has action=adjust parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'adjust') {
        openAdjustStockDialog();
    }
});
</script>

<template>
    <Head :title="inventory.product?.name || 'Inventory'" />

    <AppSidebarLayout :title="inventory.product?.name || 'Inventory'" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <h1 class="text-2xl font-bold">{{ inventory.product?.name || 'Inventory' }}</h1>
                        <Tag 
                            :value="getStockStatus().label" 
                            :severity="getStockStatus().severity"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="text-sm bg-muted px-2 py-1 rounded">{{ inventory.product?.sku || 'N/A' }}</code>
                        <Tag :value="inventory.location || 'main'" severity="info" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button 
                        label="Adjust Stock" 
                        icon="pi pi-plus-minus"
                        severity="success"
                        @click="openAdjustStockDialog"
                    />
                    <Button 
                        label="Edit" 
                        icon="pi pi-pencil"
                        severity="info"
                        @click="handleEdit"
                    />
                    <Button 
                        label="Delete" 
                        icon="pi pi-trash"
                        severity="danger"
                        outlined
                        @click="handleDelete"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Stock Information -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Stock Information</h2>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="text-sm text-muted-foreground">Total Quantity</label>
                                <p class="mt-1 text-2xl font-bold">{{ inventory.quantity }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Reserved</label>
                                <p class="mt-1 text-2xl font-bold text-yellow-600">{{ inventory.reserved_quantity || 0 }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Available</label>
                                <p class="mt-1 text-2xl font-bold text-green-600">{{ inventory.available_quantity }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Low Stock Threshold</label>
                                <p class="mt-1 text-lg">{{ inventory.low_stock_threshold || 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-muted-foreground">Cost Price</label>
                                <p class="mt-1 text-lg font-medium">
                                    {{ inventory.cost_price ? `$${parseFloat(inventory.cost_price).toFixed(2)}` : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Batch Number</label>
                                <p class="mt-1 text-lg">{{ inventory.batch_number || 'N/A' }}</p>
                            </div>
                        </div>

                        <div v-if="inventory.expiry_date" class="mt-4">
                            <label class="text-sm text-muted-foreground">Expiry Date</label>
                            <p class="mt-1 text-lg">{{ formatDate(inventory.expiry_date) }}</p>
                        </div>

                        <div v-if="inventory.notes" class="mt-4">
                            <label class="text-sm text-muted-foreground">Notes</label>
                            <p class="mt-1 text-sm">{{ inventory.notes }}</p>
                        </div>
                    </div>

                    <!-- Stock Movements -->
                    <div v-if="inventory.stock_movements && inventory.stock_movements.length > 0" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Stock Movement History</h2>
                        
                        <DataTable :value="inventory.stock_movements" class="p-datatable-sm" stripedRows>
                            <Column field="type" header="Type" style="width: 120px">
                                <template #body="slotProps">
                                    <Tag 
                                        :value="slotProps.data.type_label || slotProps.data.type" 
                                        :severity="slotProps.data.type === 'in' ? 'success' : slotProps.data.type === 'out' ? 'danger' : 'info'"
                                    />
                                </template>
                            </Column>
                            <Column field="quantity" header="Quantity" style="width: 100px">
                                <template #body="slotProps">
                                    <span class="font-medium">{{ slotProps.data.quantity }}</span>
                                </template>
                            </Column>
                            <Column header="Before" style="width: 100px">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.quantity_before }}</span>
                                </template>
                            </Column>
                            <Column header="After" style="width: 100px">
                                <template #body="slotProps">
                                    <span class="text-sm font-medium">{{ slotProps.data.quantity_after }}</span>
                                </template>
                            </Column>
                            <Column field="reason" header="Reason" style="width: 150px">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.reason || 'N/A' }}</span>
                                </template>
                            </Column>
                            <Column field="user.name" header="User" style="width: 120px">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.user?.name || 'System' }}</span>
                                </template>
                            </Column>
                            <Column field="created_at" header="Date" style="width: 150px">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ formatDate(slotProps.data.created_at) }}</span>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                    <div v-else class="rounded-lg border border-border bg-card p-6">
                        <p class="text-gray-500 text-center">No stock movements recorded</p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Product Information -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Product Information</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-muted-foreground">Product Name</label>
                                <p class="mt-1 font-medium">{{ inventory.product?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">SKU</label>
                                <p class="mt-1">{{ inventory.product?.sku || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Location</label>
                                <p class="mt-1">{{ inventory.location || 'main' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Timestamps</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-muted-foreground">Created At</label>
                                <p class="mt-1 text-sm">{{ formatDate(inventory.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Updated At</label>
                                <p class="mt-1 text-sm">{{ formatDate(inventory.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adjust Stock Dialog -->
        <Dialog 
            v-model:visible="showAdjustStockDialog" 
            modal 
            header="Adjust Stock" 
            :style="{ width: '500px' }"
            :closable="true"
        >
            <form @submit.prevent="submitAdjustStock" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium">Type <span class="text-red-500">*</span></label>
                    <Dropdown 
                        v-model="adjustForm.type" 
                        :options="typeOptions"
                        optionLabel="label" 
                        optionValue="value" 
                        :class="{ 'p-invalid': adjustForm.errors.type }"
                        class="w-full"
                    />
                    <InputError :message="adjustForm.errors.type" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium">Quantity <span class="text-red-500">*</span></label>
                    <InputNumber 
                        v-model="adjustForm.quantity" 
                        :min="adjustForm.type === 'out' ? 1 : 0"
                        :class="{ 'p-invalid': adjustForm.errors.quantity }"
                        class="w-full"
                    />
                    <small class="text-muted-foreground">
                        {{ adjustForm.type === 'in' ? 'Positive number to add stock' : adjustForm.type === 'out' ? 'Positive number to remove stock' : 'Positive or negative number to adjust' }}
                    </small>
                    <InputError :message="adjustForm.errors.quantity" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium">Reason</label>
                    <Textarea 
                        v-model="adjustForm.reason" 
                        placeholder="Enter reason for adjustment..."
                        rows="2"
                        :class="{ 'p-invalid': adjustForm.errors.reason }"
                        class="w-full"
                    />
                    <InputError :message="adjustForm.errors.reason" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium">Notes</label>
                    <Textarea 
                        v-model="adjustForm.notes" 
                        placeholder="Enter notes..."
                        rows="2"
                        :class="{ 'p-invalid': adjustForm.errors.notes }"
                        class="w-full"
                    />
                    <InputError :message="adjustForm.errors.notes" />
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <Button type="button" label="Cancel" severity="secondary" outlined @click="closeAdjustStockDialog" />
                    <Button type="submit" label="Adjust Stock" icon="pi pi-check" :loading="adjustForm.processing" />
                </div>
            </form>
        </Dialog>
    </AppSidebarLayout>
</template>

