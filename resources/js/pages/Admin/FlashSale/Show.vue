<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputSwitch from 'primevue/inputswitch';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { computed } from 'vue';
import FlashSaleController from '@/actions/App/Http/Controllers/Admin/FlashSale/FlashSaleController';

const props = defineProps({
    flashSale: {
        type: Object,
        required: true
    }
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Flash Sales',
        href: FlashSaleController.index.url(),
    },
    {
        title: props.flashSale.name,
        href: FlashSaleController.show.url(props.flashSale.id),
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

const formatDiscount = () => {
    if (!props.flashSale.discount_value) return 'N/A';
    if (props.flashSale.discount_type === 'percentage') {
        return `${props.flashSale.discount_value}%`;
    }
    return `$${parseFloat(props.flashSale.discount_value).toFixed(2)}`;
};

const getStatusTag = computed(() => {
    const now = new Date();
    const start = new Date(props.flashSale.start_date);
    const end = new Date(props.flashSale.end_date);
    
    if (now < start) {
        return { label: 'Upcoming', severity: 'info' };
    } else if (now > end) {
        return { label: 'Expired', severity: 'danger' };
    } else if (props.flashSale.is_active) {
        return { label: 'Ongoing', severity: 'success' };
    } else {
        return { label: 'Inactive', severity: 'secondary' };
    }
});

const formatProductDiscount = (product) => {
    if (product.pivot.discount_type && product.pivot.discount_value) {
        if (product.pivot.discount_type === 'percentage') {
            return `${product.pivot.discount_value}%`;
        }
        return `$${parseFloat(product.pivot.discount_value).toFixed(2)}`;
    }
    return formatDiscount();
};

const handleEdit = () => {
    router.visit(FlashSaleController.edit.url(props.flashSale.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this flash sale?')) {
        router.delete(FlashSaleController.destroy.url(props.flashSale.id));
    }
};

const handleToggleStatus = () => {
    router.post(FlashSaleController.toggleStatus.url(props.flashSale.id), {}, {
        preserveScroll: true,
    });
};

const handleToggleFeatured = () => {
    router.post(FlashSaleController.toggleFeatured.url(props.flashSale.id), {}, {
        preserveScroll: true,
    });
};

const handleImageError = (event) => {
    event.target.style.display = 'none';
};
</script>

<template>
    <Head :title="flashSale.name" />

    <AppSidebarLayout :title="flashSale.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div v-if="flashSale.banner_image_url" class="h-24 w-24 rounded-lg overflow-hidden bg-gray-100">
                        <img 
                            :src="flashSale.banner_image_url" 
                            :alt="flashSale.name"
                            class="h-full w-full object-cover"
                            @error="handleImageError"
                        />
                    </div>
                    <div v-else class="flex h-24 w-24 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="pi pi-image text-3xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-2xl font-bold">{{ flashSale.name }}</h1>
                            <Tag 
                                :value="getStatusTag.label" 
                                :severity="getStatusTag.severity"
                            />
                            <Tag 
                                v-if="flashSale.is_featured"
                                value="Featured"
                                severity="warning"
                            />
                        </div>
                        <code class="text-sm bg-muted px-2 py-1 rounded">{{ flashSale.slug }}</code>
                    </div>
                </div>
                <div class="flex gap-2">
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
                    <!-- Basic Information -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
                        
                        <div class="space-y-4">
                            <div v-if="flashSale.description">
                                <label class="text-sm text-muted-foreground">Description</label>
                                <p class="mt-1 text-sm">{{ flashSale.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Start Date</label>
                                    <p class="mt-1 font-medium">{{ formatDate(flashSale.start_date) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">End Date</label>
                                    <p class="mt-1 font-medium">{{ formatDate(flashSale.end_date) }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Discount Type</label>
                                    <div class="mt-1">
                                        <Tag 
                                            :value="flashSale.discount_type === 'percentage' ? 'Percentage' : 'Fixed'" 
                                            :severity="flashSale.discount_type === 'percentage' ? 'success' : 'warning'"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Discount Value</label>
                                    <p class="mt-1 font-medium text-lg">{{ formatDiscount() }}</p>
                                </div>
                            </div>

                            <div v-if="flashSale.max_products">
                                <label class="text-sm text-muted-foreground">Max Products</label>
                                <p class="mt-1 font-medium">{{ flashSale.max_products }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div v-if="flashSale.products && flashSale.products.length > 0" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Products ({{ flashSale.products.length }})</h2>
                        
                        <DataTable :value="flashSale.products" class="p-datatable-sm" stripedRows>
                            <Column field="name" header="Product Name">
                                <template #body="slotProps">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ slotProps.data.name }}</span>
                                        <span class="text-xs text-gray-500">{{ slotProps.data.sku }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column header="Price" style="width: 100px">
                                <template #body="slotProps">
                                    <span class="font-medium">${{ parseFloat(slotProps.data.price).toFixed(2) }}</span>
                                </template>
                            </Column>
                            <Column header="Discount" style="width: 120px">
                                <template #body="slotProps">
                                    <span class="font-medium">{{ formatProductDiscount(slotProps.data) }}</span>
                                </template>
                            </Column>
                            <Column header="Sort Order" style="width: 100px">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.pivot.sort_order }}</span>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                    <div v-else class="rounded-lg border border-border bg-card p-6">
                        <p class="text-gray-500 text-center">No products added to this flash sale</p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status & Flags -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Status & Flags</h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium">Active</label>
                                <InputSwitch 
                                    :modelValue="flashSale.is_active" 
                                    @update:modelValue="handleToggleStatus"
                                />
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium">Featured</label>
                                <InputSwitch 
                                    :modelValue="flashSale.is_featured" 
                                    @update:modelValue="handleToggleFeatured"
                                />
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Sort Order</label>
                                <p class="mt-1">{{ flashSale.sort_order }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Timestamps</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-muted-foreground">Created At</label>
                                <p class="mt-1 text-sm">{{ formatDate(flashSale.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Updated At</label>
                                <p class="mt-1 text-sm">{{ formatDate(flashSale.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

