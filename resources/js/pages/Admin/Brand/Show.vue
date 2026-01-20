<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputSwitch from 'primevue/inputswitch';
import { computed } from 'vue';
import BrandController from '@/actions/App/Http/Controllers/Admin/Brand/BrandController';

const props = defineProps({
    brand: {
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
        title: 'Brands',
        href: BrandController.index.url(),
    },
    {
        title: props.brand.name,
        href: BrandController.show.url(props.brand.id),
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

const handleEdit = () => {
    router.visit(BrandController.edit.url(props.brand.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this brand?')) {
        router.delete(BrandController.destroy.url(props.brand.id));
    }
};

const handleToggleStatus = () => {
    router.post(BrandController.toggleStatus.url(props.brand.id), {}, {
        preserveScroll: true,
    });
};

const handleToggleFeatured = () => {
    router.post(BrandController.toggleFeatured.url(props.brand.id), {}, {
        preserveScroll: true,
    });
};

const handleImageError = (event) => {
    event.target.style.display = 'none';
};
</script>

<template>
    <Head :title="brand.name" />

    <AppSidebarLayout :title="brand.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div v-if="brand.main_image_url" class="h-24 w-24 rounded-lg overflow-hidden bg-gray-100">
                        <img 
                            :src="brand.main_image_url" 
                            :alt="brand.name"
                            class="h-full w-full object-cover"
                            @error="handleImageError"
                        />
                    </div>
                    <div v-else class="flex h-24 w-24 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="pi pi-image text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ brand.name }}</h1>
                        <code class="text-sm bg-muted px-2 py-1 rounded mt-1">{{ brand.slug }}</code>
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
                            <div>
                                <label class="text-sm text-muted-foreground">Brand Name</label>
                                <p class="mt-1 font-medium text-lg">{{ brand.name }}</p>
                            </div>

                            <div v-if="brand.description">
                                <label class="text-sm text-muted-foreground">Description</label>
                                <p class="mt-1 text-sm">{{ brand.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">ID</label>
                                    <p class="mt-1 font-mono">#{{ brand.id }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Sort Order</label>
                                    <p class="mt-1 font-mono">{{ brand.sort_order }}</p>
                                </div>
                            </div>

                            <div v-if="brand.website">
                                <label class="text-sm text-muted-foreground">Website</label>
                                <p class="mt-1">
                                    <a :href="brand.website" target="_blank" rel="noopener noreferrer" 
                                       class="text-blue-600 hover:underline">
                                        {{ brand.website }}
                                        <i class="pi pi-external-link ml-1 text-xs"></i>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Settings</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Status</label>
                                    <div class="mt-1">
                                        <InputSwitch 
                                            :modelValue="brand.is_active" 
                                            @update:modelValue="handleToggleStatus"
                                        />
                                        <span class="ml-2 text-sm">
                                            {{ brand.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Featured</label>
                                    <div class="mt-1">
                                        <InputSwitch 
                                            :modelValue="brand.is_featured" 
                                            @update:modelValue="handleToggleFeatured"
                                        />
                                        <span class="ml-2 text-sm">
                                            {{ brand.is_featured ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Information -->
                    <div v-if="brand.meta_title || brand.meta_description || brand.meta_keywords" 
                         class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">SEO Information</h2>
                        
                        <div class="space-y-4">
                            <div v-if="brand.meta_title">
                                <label class="text-sm text-muted-foreground">Meta Title</label>
                                <p class="mt-1 text-sm">{{ brand.meta_title }}</p>
                            </div>

                            <div v-if="brand.meta_description">
                                <label class="text-sm text-muted-foreground">Meta Description</label>
                                <p class="mt-1 text-sm">{{ brand.meta_description }}</p>
                            </div>

                            <div v-if="brand.meta_keywords">
                                <label class="text-sm text-muted-foreground">Meta Keywords</label>
                                <p class="mt-1 text-sm">{{ brand.meta_keywords }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div v-if="brand.main_image_url || (brand.gallery_images_urls && brand.gallery_images_urls.length > 0)" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Images</h2>
                        
                        <div class="space-y-4">
                            <div v-if="brand.main_image_url">
                                <label class="text-sm text-muted-foreground mb-2 block">Main Image</label>
                                <img 
                                    :src="brand.main_image_url" 
                                    :alt="brand.name"
                                    class="h-48 w-48 object-cover rounded-lg border"
                                    @error="handleImageError"
                                />
                            </div>

                            <div v-if="brand.gallery_images_urls && brand.gallery_images_urls.length > 0">
                                <label class="text-sm text-muted-foreground mb-2 block">Gallery Images</label>
                                <div class="flex flex-wrap gap-2">
                                    <img 
                                        v-for="(image, index) in brand.gallery_images_urls" 
                                        :key="index"
                                        :src="image" 
                                        :alt="`${brand.name} - Image ${index + 1}`"
                                        class="h-32 w-32 object-cover rounded-lg border"
                                        @error="handleImageError"
                                    />
                                </div>
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
                                <p class="font-medium">{{ formatDate(brand.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-muted-foreground">Last Updated</label>
                                <p class="font-medium">{{ formatDate(brand.updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                        
                        <div class="space-y-2">
                            <Button 
                                :label="brand.is_active ? 'Deactivate' : 'Activate'"
                                :icon="brand.is_active ? 'pi pi-times-circle' : 'pi pi-check-circle'"
                                :severity="brand.is_active ? 'secondary' : 'success'"
                                class="w-full"
                                @click="handleToggleStatus"
                            />
                            <Button 
                                :label="brand.is_featured ? 'Unfeature' : 'Feature'"
                                :icon="brand.is_featured ? 'pi pi-star-fill' : 'pi pi-star'"
                                :severity="brand.is_featured ? 'warning' : 'secondary'"
                                class="w-full"
                                @click="handleToggleFeatured"
                            />
                            <Button 
                                label="Edit Brand"
                                icon="pi pi-pencil"
                                severity="info"
                                class="w-full"
                                @click="handleEdit"
                            />
                            <Button 
                                label="Delete Brand"
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

