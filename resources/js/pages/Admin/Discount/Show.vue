<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputSwitch from 'primevue/inputswitch';
import Tag from 'primevue/tag';
import { computed } from 'vue';
import DiscountController from '@/actions/App/Http/Controllers/Admin/Discount/DiscountController';

const props = defineProps({
    discount: {
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
        title: 'Discounts',
        href: DiscountController.index.url(),
    },
    {
        title: props.discount.name,
        href: DiscountController.show.url(props.discount.id),
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

const formatValue = () => {
    if (props.discount.type === 'percentage') {
        return `${props.discount.value}%`;
    }
    return `$${parseFloat(props.discount.value).toFixed(2)}`;
};

const isExpired = computed(() => {
    return new Date(props.discount.end_date) < new Date();
});

const isValid = computed(() => {
    if (!props.discount.is_active) return false;
    const now = new Date();
    const start = new Date(props.discount.start_date);
    const end = new Date(props.discount.end_date);
    return now >= start && now <= end;
});

const handleEdit = () => {
    router.visit(DiscountController.edit.url(props.discount.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this discount?')) {
        router.delete(DiscountController.destroy.url(props.discount.id));
    }
};

const handleToggleStatus = () => {
    router.post(DiscountController.toggleStatus.url(props.discount.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="discount.name" />

    <AppSidebarLayout :title="discount.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <h1 class="text-2xl font-bold">{{ discount.name }}</h1>
                        <Tag :value="discount.code" severity="info" />
                        <Tag 
                            v-if="isExpired"
                            value="Expired"
                            severity="danger"
                        />
                        <Tag 
                            v-else-if="isValid"
                            value="Active"
                            severity="success"
                        />
                        <Tag 
                            v-else
                            value="Inactive"
                            severity="secondary"
                        />
                    </div>
                    <code class="text-sm bg-muted px-2 py-1 rounded">{{ discount.code }}</code>
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
                            <div>
                                <label class="text-sm text-muted-foreground">Discount Name</label>
                                <p class="mt-1 font-medium text-lg">{{ discount.name }}</p>
                            </div>

                            <div v-if="discount.description">
                                <label class="text-sm text-muted-foreground">Description</label>
                                <p class="mt-1 text-sm">{{ discount.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Type</label>
                                    <div class="mt-1">
                                        <Tag 
                                            :value="discount.type === 'percentage' ? 'Percentage' : 'Fixed'" 
                                            :severity="discount.type === 'percentage' ? 'success' : 'warning'"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Value</label>
                                    <p class="mt-1 font-medium text-lg">{{ formatValue() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Details -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Discount Details</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Minimum Purchase</label>
                                    <p class="mt-1 font-medium">
                                        {{ discount.minimum_purchase ? `$${parseFloat(discount.minimum_purchase).toFixed(2)}` : 'No minimum' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Maximum Discount</label>
                                    <p class="mt-1 font-medium">
                                        {{ discount.maximum_discount ? `$${parseFloat(discount.maximum_discount).toFixed(2)}` : 'No limit' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Usage Limit Per User</label>
                                    <p class="mt-1 font-medium">
                                        {{ discount.usage_limit_per_user || 'Unlimited' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Total Usage Limit</label>
                                    <p class="mt-1 font-medium">
                                        {{ discount.total_usage_limit || 'Unlimited' }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm text-muted-foreground">Used Count</label>
                                <p class="mt-1 font-medium">{{ discount.used_count || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Validity Period -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Validity Period</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Start Date</label>
                                    <p class="mt-1 font-medium">{{ formatDate(discount.start_date) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">End Date</label>
                                    <p class="mt-1 font-medium">{{ formatDate(discount.end_date) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Settings</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Status</label>
                                    <div class="mt-1">
                                        <InputSwitch 
                                            :modelValue="discount.is_active" 
                                            @update:modelValue="handleToggleStatus"
                                        />
                                        <span class="ml-2 text-sm">
                                            {{ discount.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">First Time Only</label>
                                    <div class="mt-1">
                                        <Tag 
                                            :value="discount.is_first_time_only ? 'Yes' : 'No'" 
                                            :severity="discount.is_first_time_only ? 'warning' : 'secondary'"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Free Shipping</label>
                                    <div class="mt-1">
                                        <Tag 
                                            :value="discount.free_shipping ? 'Yes' : 'No'" 
                                            :severity="discount.free_shipping ? 'success' : 'secondary'"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Information -->
                    <div v-if="discount.meta_title || discount.meta_description || discount.meta_keywords" 
                         class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">SEO Information</h2>
                        
                        <div class="space-y-4">
                            <div v-if="discount.meta_title">
                                <label class="text-sm text-muted-foreground">Meta Title</label>
                                <p class="mt-1 text-sm">{{ discount.meta_title }}</p>
                            </div>

                            <div v-if="discount.meta_description">
                                <label class="text-sm text-muted-foreground">Meta Description</label>
                                <p class="mt-1 text-sm">{{ discount.meta_description }}</p>
                            </div>

                            <div v-if="discount.meta_keywords">
                                <label class="text-sm text-muted-foreground">Meta Keywords</label>
                                <p class="mt-1 text-sm">{{ discount.meta_keywords }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Timestamps -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Timestamps</h2>
                        
                        <div class="space-y-3 text-sm">
                            <div>
                                <label class="text-muted-foreground">Created</label>
                                <p class="font-medium">{{ formatDate(discount.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-muted-foreground">Last Updated</label>
                                <p class="font-medium">{{ formatDate(discount.updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                        
                        <div class="space-y-2">
                            <Button 
                                :label="discount.is_active ? 'Deactivate' : 'Activate'"
                                :icon="discount.is_active ? 'pi pi-times-circle' : 'pi pi-check-circle'"
                                :severity="discount.is_active ? 'secondary' : 'success'"
                                class="w-full"
                                @click="handleToggleStatus"
                            />
                            <Button 
                                label="Edit Discount"
                                icon="pi pi-pencil"
                                severity="info"
                                class="w-full"
                                @click="handleEdit"
                            />
                            <Button 
                                label="Delete Discount"
                                icon="pi pi-trash"
                                severity="danger"
                                outlined
                                class="w-full"
                                @click="handleDelete"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

