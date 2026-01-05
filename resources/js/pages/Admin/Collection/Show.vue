<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputSwitch from 'primevue/inputswitch';
import { computed } from 'vue';
import CollectionController from '@/actions/App/Http/Controllers/Admin/Collection/CollectionController';

const props = defineProps({
    collection: {
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
        title: 'Collections',
        href: CollectionController.index.url(),
    },
    {
        title: props.collection.name,
        href: CollectionController.show.url(props.collection.id),
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
    router.visit(CollectionController.edit.url(props.collection.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this collection?')) {
        router.delete(CollectionController.destroy.url(props.collection.id));
    }
};

const handleToggleStatus = () => {
    router.post(CollectionController.toggleStatus.url(props.collection.id), {}, {
        preserveScroll: true,
    });
};

const handleToggleFeatured = () => {
    router.post(CollectionController.toggleFeatured.url(props.collection.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="collection.name" />

    <AppSidebarLayout :title="collection.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img 
                        v-if="collection.image_url" 
                        :src="collection.image_url" 
                        :alt="collection.name"
                        class="w-16 h-16 object-contain border rounded-lg"
                        @error="$event.target.style.display = 'none'"
                    />
                    <div>
                        <h1 class="text-2xl font-bold">{{ collection.name }}</h1>
                        <code class="text-sm bg-muted px-2 py-1 rounded mt-1">{{ collection.slug }}</code>
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

            <!-- Banner Image -->
            <div v-if="collection.banner_url" class="rounded-lg border border-border bg-card overflow-hidden">
                <img 
                    :src="collection.banner_url" 
                    :alt="collection.name + ' Banner'"
                    class="w-full h-64 object-cover"
                    @error="$event.target.style.display = 'none'"
                />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-muted-foreground">Collection Name</label>
                                <p class="mt-1 font-medium text-lg">{{ collection.name }}</p>
                            </div>

                            <div v-if="collection.description">
                                <label class="text-sm text-muted-foreground">Description</label>
                                <p class="mt-1 text-sm">{{ collection.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">ID</label>
                                    <p class="mt-1 font-mono">#{{ collection.id }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Sort Order</label>
                                    <p class="mt-1 font-mono">{{ collection.sort_order }}</p>
                                </div>
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
                                            :modelValue="collection.is_active" 
                                            @update:modelValue="handleToggleStatus"
                                        />
                                        <span class="ml-2 text-sm">
                                            {{ collection.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Featured</label>
                                    <div class="mt-1">
                                        <InputSwitch 
                                            :modelValue="collection.is_featured" 
                                            @update:modelValue="handleToggleFeatured"
                                        />
                                        <span class="ml-2 text-sm">
                                            {{ collection.is_featured ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Information -->
                    <div v-if="collection.meta_title || collection.meta_description || collection.meta_keywords" 
                         class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">SEO Information</h2>
                        
                        <div class="space-y-4">
                            <div v-if="collection.meta_title">
                                <label class="text-sm text-muted-foreground">Meta Title</label>
                                <p class="mt-1 text-sm">{{ collection.meta_title }}</p>
                            </div>

                            <div v-if="collection.meta_description">
                                <label class="text-sm text-muted-foreground">Meta Description</label>
                                <p class="mt-1 text-sm">{{ collection.meta_description }}</p>
                            </div>

                            <div v-if="collection.meta_keywords">
                                <label class="text-sm text-muted-foreground">Meta Keywords</label>
                                <p class="mt-1 text-sm">{{ collection.meta_keywords }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Collection Image -->
                    <div v-if="collection.image_url" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Collection Image</h2>
                        <div class="flex justify-center">
                            <img 
                                :src="collection.image_url" 
                                :alt="collection.name"
                                class="w-full max-w-xs object-contain rounded-lg"
                                @error="$event.target.style.display = 'none'"
                            />
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Timestamps</h2>
                        
                        <div class="space-y-3 text-sm">
                            <div>
                                <label class="text-muted-foreground">Created</label>
                                <p class="font-medium">{{ formatDate(collection.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-muted-foreground">Last Updated</label>
                                <p class="font-medium">{{ formatDate(collection.updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                        
                        <div class="space-y-2">
                            <Button 
                                :label="collection.is_active ? 'Deactivate' : 'Activate'"
                                :icon="collection.is_active ? 'pi pi-times-circle' : 'pi pi-check-circle'"
                                :severity="collection.is_active ? 'secondary' : 'success'"
                                class="w-full"
                                @click="handleToggleStatus"
                            />
                            <Button 
                                :label="collection.is_featured ? 'Unfeature' : 'Feature'"
                                :icon="collection.is_featured ? 'pi pi-star-fill' : 'pi pi-star'"
                                :severity="collection.is_featured ? 'warning' : 'secondary'"
                                class="w-full"
                                @click="handleToggleFeatured"
                            />
                            <Button 
                                label="Edit Collection"
                                icon="pi pi-pencil"
                                severity="info"
                                class="w-full"
                                @click="handleEdit"
                            />
                            <Button 
                                label="Delete Collection"
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

