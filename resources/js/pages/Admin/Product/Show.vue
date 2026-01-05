<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputSwitch from 'primevue/inputswitch';
import Tag from 'primevue/tag';
import { computed } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/Admin/Product/ProductController';

const props = defineProps({
    product: {
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
        title: 'Products',
        href: ProductController.index.url(),
    },
    {
        title: props.product.name,
        href: ProductController.show.url(props.product.id),
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

const formatPrice = (price) => {
    return `$${parseFloat(price).toFixed(2)}`;
};

const getStockStatus = () => {
    if (!props.product.track_inventory) return { label: 'N/A', severity: 'secondary' };
    if (props.product.stock_quantity <= 0 && !props.product.allow_backorder) {
        return { label: 'Out of Stock', severity: 'danger' };
    }
    if (props.product.low_stock_threshold && props.product.stock_quantity <= props.product.low_stock_threshold) {
        return { label: 'Low Stock', severity: 'warning' };
    }
    return { label: 'In Stock', severity: 'success' };
};

const handleEdit = () => {
    router.visit(ProductController.edit.url(props.product.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(ProductController.destroy.url(props.product.id));
    }
};

const handleToggleStatus = () => {
    router.post(ProductController.toggleStatus.url(props.product.id), {}, {
        preserveScroll: true,
    });
};

const handleToggleFeatured = () => {
    router.post(ProductController.toggleFeatured.url(props.product.id), {}, {
        preserveScroll: true,
    });
};

const handleImageError = (event) => {
    event.target.style.display = 'none';
};
</script>

<template>
    <Head :title="product.name" />

    <AppSidebarLayout :title="product.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div v-if="product.main_image_url" class="h-24 w-24 rounded-lg overflow-hidden bg-gray-100">
                        <img 
                            :src="product.main_image_url" 
                            :alt="product.name"
                            class="h-full w-full object-cover"
                            @error="handleImageError"
                        />
                    </div>
                    <div v-else class="flex h-24 w-24 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="pi pi-image text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ product.name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <code class="text-sm bg-muted px-2 py-1 rounded">{{ product.sku }}</code>
                            <code class="text-sm bg-muted px-2 py-1 rounded">{{ product.slug }}</code>
                        </div>
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
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Status</label>
                                    <div class="mt-1">
                                        <Tag 
                                            :value="product.is_active ? 'Active' : 'Inactive'" 
                                            :severity="product.is_active ? 'success' : 'secondary'"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Stock Status</label>
                                    <div class="mt-1">
                                        <Tag 
                                            :value="getStockStatus().label" 
                                            :severity="getStockStatus().severity"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div v-if="product.short_description">
                                <label class="text-sm text-muted-foreground">Short Description</label>
                                <p class="mt-1 text-sm">{{ product.short_description }}</p>
                            </div>

                            <div v-if="product.description">
                                <label class="text-sm text-muted-foreground">Description</label>
                                <div class="mt-1 text-sm whitespace-pre-wrap">{{ product.description }}</div>
                            </div>

                            <div v-if="product.specifications">
                                <label class="text-sm text-muted-foreground">Specifications</label>
                                <div class="mt-1 text-sm whitespace-pre-wrap">{{ product.specifications }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Pricing</h2>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="text-sm text-muted-foreground">Price</label>
                                <p class="mt-1 text-lg font-semibold">{{ formatPrice(product.price) }}</p>
                            </div>
                            <div v-if="product.compare_at_price">
                                <label class="text-sm text-muted-foreground">Compare At Price</label>
                                <p class="mt-1 text-lg line-through text-gray-400">{{ formatPrice(product.compare_at_price) }}</p>
                                <p class="text-sm text-green-600">Save {{ product.discount_percentage }}%</p>
                            </div>
                            <div v-if="product.cost_price">
                                <label class="text-sm text-muted-foreground">Cost Price</label>
                                <p class="mt-1 text-lg">{{ formatPrice(product.cost_price) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Inventory</h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-muted-foreground">Stock Quantity</label>
                                <p class="mt-1 text-lg font-semibold">{{ product.stock_quantity }}</p>
                            </div>
                            <div v-if="product.low_stock_threshold">
                                <label class="text-sm text-muted-foreground">Low Stock Threshold</label>
                                <p class="mt-1 text-lg">{{ product.low_stock_threshold }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Track Inventory</label>
                                <p class="mt-1">{{ product.track_inventory ? 'Yes' : 'No' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Allow Backorder</label>
                                <p class="mt-1">{{ product.allow_backorder ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div v-if="product.main_image_url || (product.gallery_images_urls && product.gallery_images_urls.length > 0)" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Images</h2>
                        
                        <div class="space-y-4">
                            <div v-if="product.main_image_url">
                                <label class="text-sm text-muted-foreground mb-2 block">Main Image</label>
                                <img 
                                    :src="product.main_image_url" 
                                    :alt="product.name"
                                    class="h-48 w-48 object-cover rounded-lg border"
                                    @error="handleImageError"
                                />
                            </div>

                            <div v-if="product.gallery_images_urls && product.gallery_images_urls.length > 0">
                                <label class="text-sm text-muted-foreground mb-2 block">Gallery Images</label>
                                <div class="flex flex-wrap gap-2">
                                    <img 
                                        v-for="(image, index) in product.gallery_images_urls" 
                                        :key="index"
                                        :src="image" 
                                        :alt="`${product.name} - Image ${index + 1}`"
                                        class="h-32 w-32 object-cover rounded-lg border"
                                        @error="handleImageError"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Attributes -->
                    <div v-if="product.attributes && product.attributes.length > 0" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Product Attributes</h2>
                        
                        <div class="space-y-3">
                            <div 
                                v-for="(attr, index) in product.attributes" 
                                :key="index"
                                class="flex items-center justify-between p-3 border rounded-lg"
                            >
                                <div class="flex-1">
                                    <p class="font-medium">{{ attr.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        <span v-if="attr.pivot.attribute_value_id && attr.values">
                                            {{ attr.values.find(v => v.id === attr.pivot.attribute_value_id)?.display_value || attr.values.find(v => v.id === attr.pivot.attribute_value_id)?.value || 'N/A' }}
                                        </span>
                                        <span v-else-if="attr.pivot.value">
                                            {{ attr.pivot.value }}
                                        </span>
                                        <span v-else>N/A</span>
                                    </p>
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    Sort: {{ attr.pivot.sort_order }}
                                </div>
                            </div>
                        </div>
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
                                    :modelValue="product.is_active" 
                                    @update:modelValue="handleToggleStatus"
                                />
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium">Featured</label>
                                <InputSwitch 
                                    :modelValue="product.is_featured" 
                                    @update:modelValue="handleToggleFeatured"
                                />
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium">New</label>
                                <Tag :value="product.is_new ? 'Yes' : 'No'" :severity="product.is_new ? 'success' : 'secondary'" />
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium">Bestseller</label>
                                <Tag :value="product.is_bestseller ? 'Yes' : 'No'" :severity="product.is_bestseller ? 'success' : 'secondary'" />
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium">Digital</label>
                                <Tag :value="product.is_digital ? 'Yes' : 'No'" :severity="product.is_digital ? 'success' : 'secondary'" />
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium">Virtual</label>
                                <Tag :value="product.is_virtual ? 'Yes' : 'No'" :severity="product.is_virtual ? 'success' : 'secondary'" />
                            </div>
                        </div>
                    </div>

                    <!-- Relationships -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Relationships</h2>
                        
                        <div class="space-y-3">
                            <div v-if="product.category">
                                <label class="text-sm text-muted-foreground">Category</label>
                                <p class="mt-1 font-medium">{{ product.category.name }}</p>
                            </div>
                            <div v-if="product.brand">
                                <label class="text-sm text-muted-foreground">Brand</label>
                                <p class="mt-1 font-medium">{{ product.brand.name }}</p>
                            </div>
                            <div v-if="product.collection">
                                <label class="text-sm text-muted-foreground">Collection</label>
                                <p class="mt-1 font-medium">{{ product.collection.name }}</p>
                            </div>
                            <div v-if="product.tags && product.tags.length > 0">
                                <label class="text-sm text-muted-foreground">Tags</label>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <Tag 
                                        v-for="tag in product.tags" 
                                        :key="tag.id"
                                        :value="tag.name"
                                        :style="{ backgroundColor: tag.color, color: '#fff' }"
                                    />
                                </div>
                            </div>
                            <div v-if="product.labels && product.labels.length > 0">
                                <label class="text-sm text-muted-foreground">Labels</label>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <Tag 
                                        v-for="label in product.labels" 
                                        :key="label.id"
                                        :value="label.name"
                                        severity="info"
                                    />
                                </div>
                            </div>
                            <div v-if="product.discounts && product.discounts.length > 0">
                                <label class="text-sm text-muted-foreground">Discounts</label>
                                <div class="mt-1 space-y-1">
                                    <div v-for="discount in product.discounts" :key="discount.id" class="text-sm">
                                        {{ discount.name }} ({{ discount.code }})
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Physical Attributes -->
                    <div v-if="product.weight || product.length || product.width || product.height" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Physical Attributes</h2>
                        
                        <div class="space-y-3">
                            <div v-if="product.weight">
                                <label class="text-sm text-muted-foreground">Weight</label>
                                <p class="mt-1">{{ product.weight }} {{ product.weight_unit }}</p>
                            </div>
                            <div v-if="product.length || product.width || product.height">
                                <label class="text-sm text-muted-foreground">Dimensions</label>
                                <p class="mt-1">
                                    <span v-if="product.length">{{ product.length }}</span>
                                    <span v-if="product.width"> × {{ product.width }}</span>
                                    <span v-if="product.height"> × {{ product.height }}</span>
                                    <span v-if="product.length || product.width || product.height"> {{ product.dimension_unit }}</span>
                                </p>
                            </div>
                            <div v-if="product.shipping_weight">
                                <label class="text-sm text-muted-foreground">Shipping Weight</label>
                                <p class="mt-1">{{ product.shipping_weight }} {{ product.weight_unit }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tax & Shipping -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Tax & Shipping</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-muted-foreground">Taxable</label>
                                <p class="mt-1">{{ product.taxable ? 'Yes' : 'No' }}</p>
                            </div>
                            <div v-if="product.tax_rate">
                                <label class="text-sm text-muted-foreground">Tax Rate</label>
                                <p class="mt-1">{{ product.tax_rate }}%</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Requires Shipping</label>
                                <p class="mt-1">{{ product.requires_shipping ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Statistics</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-muted-foreground">Views</label>
                                <p class="mt-1 text-lg font-semibold">{{ product.view_count }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Sold</label>
                                <p class="mt-1 text-lg font-semibold">{{ product.sold_count }}</p>
                            </div>
                            <div v-if="product.rating">
                                <label class="text-sm text-muted-foreground">Rating</label>
                                <p class="mt-1 text-lg font-semibold">
                                    {{ product.rating }} / 5.0
                                    <span class="text-sm text-muted-foreground">({{ product.rating_count }} reviews)</span>
                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Sort Order</label>
                                <p class="mt-1">{{ product.sort_order }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Timestamps</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-muted-foreground">Created At</label>
                                <p class="mt-1 text-sm">{{ formatDate(product.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-muted-foreground">Updated At</label>
                                <p class="mt-1 text-sm">{{ formatDate(product.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

