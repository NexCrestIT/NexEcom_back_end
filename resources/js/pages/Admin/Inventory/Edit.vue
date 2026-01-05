<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import { computed } from 'vue';
import InventoryController from '@/actions/App/Http/Controllers/Admin/Inventory/InventoryController';

const props = defineProps({
    inventory: {
        type: Object,
        required: true
    }
});

const form = useForm({
    _method: 'PUT',
    quantity: props.inventory.quantity || 0,
    reserved_quantity: props.inventory.reserved_quantity || 0,
    low_stock_threshold: props.inventory.low_stock_threshold || null,
    cost_price: props.inventory.cost_price ? parseFloat(props.inventory.cost_price) : null,
    batch_number: props.inventory.batch_number || '',
    expiry_date: props.inventory.expiry_date ? new Date(props.inventory.expiry_date) : null,
    notes: props.inventory.notes || '',
    reason: '',
});

const locationOptions = [
    { label: 'Main', value: 'main' },
    { label: 'Warehouse 1', value: 'warehouse_1' },
    { label: 'Warehouse 2', value: 'warehouse_2' },
    { label: 'Store', value: 'store' },
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
        title: `Edit: ${props.inventory.product?.name || 'Inventory'}`,
        href: InventoryController.edit.url(props.inventory.id),
    },
]);

const save = () => {
    form.post(InventoryController.update.url(props.inventory.id), {
        preserveScroll: true,
    });
};

const cancel = () => {
    router.visit(InventoryController.index.url());
};
</script>

<template>
    <Head :title="`Edit: ${inventory.product?.name || 'Inventory'}`" />

    <AppSidebarLayout :title="`Edit: ${inventory.product?.name || 'Inventory'}`" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Product</label>
                                <InputText 
                                    :value="inventory.product?.name || 'N/A'" 
                                    disabled
                                    class="w-full"
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Location</label>
                                <InputText 
                                    :value="inventory.location || 'main'" 
                                    disabled
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="quantity" class="text-sm font-medium">
                                    Quantity <span class="text-red-500">*</span>
                                </label>
                                <InputNumber 
                                    id="quantity" 
                                    v-model="form.quantity" 
                                    :min="0"
                                    :class="{ 'p-invalid': form.errors.quantity }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.quantity" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="reserved_quantity" class="text-sm font-medium">Reserved Quantity</label>
                                <InputNumber 
                                    id="reserved_quantity" 
                                    v-model="form.reserved_quantity" 
                                    :min="0"
                                    :class="{ 'p-invalid': form.errors.reserved_quantity }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.reserved_quantity" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="low_stock_threshold" class="text-sm font-medium">Low Stock Threshold</label>
                                <InputNumber 
                                    id="low_stock_threshold" 
                                    v-model="form.low_stock_threshold" 
                                    :min="0"
                                    :class="{ 'p-invalid': form.errors.low_stock_threshold }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.low_stock_threshold" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="cost_price" class="text-sm font-medium">Cost Price</label>
                                <InputNumber 
                                    id="cost_price" 
                                    v-model="form.cost_price" 
                                    :min="0"
                                    prefix="$"
                                    :class="{ 'p-invalid': form.errors.cost_price }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.cost_price" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="batch_number" class="text-sm font-medium">Batch Number</label>
                                <InputText 
                                    id="batch_number" 
                                    v-model="form.batch_number" 
                                    placeholder="Enter batch number..."
                                    :class="{ 'p-invalid': form.errors.batch_number }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.batch_number" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="expiry_date" class="text-sm font-medium">Expiry Date</label>
                            <Calendar 
                                id="expiry_date" 
                                v-model="form.expiry_date" 
                                dateFormat="yy-mm-dd"
                                :class="{ 'p-invalid': form.errors.expiry_date }"
                                class="w-full"
                            />
                            <InputError :message="form.errors.expiry_date" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="reason" class="text-sm font-medium">Reason for Change</label>
                            <InputText 
                                id="reason" 
                                v-model="form.reason" 
                                placeholder="Enter reason for adjustment..."
                                :class="{ 'p-invalid': form.errors.reason }"
                                class="w-full"
                            />
                            <small class="text-muted-foreground">Required if quantity is changed</small>
                            <InputError :message="form.errors.reason" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="notes" class="text-sm font-medium">Notes</label>
                            <Textarea 
                                id="notes" 
                                v-model="form.notes" 
                                placeholder="Enter notes..."
                                rows="3"
                                :class="{ 'p-invalid': form.errors.notes }"
                                class="w-full"
                            />
                            <InputError :message="form.errors.notes" />
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-2 mt-4 pt-4 border-t">
                        <Button type="button" label="Cancel" severity="secondary" outlined @click="cancel" />
                        <Button type="submit" label="Update Inventory" icon="pi pi-check" :loading="form.processing"
                            :disabled="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

