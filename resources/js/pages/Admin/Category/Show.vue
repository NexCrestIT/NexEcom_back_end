<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { computed } from 'vue';
import CategoryController from '@/actions/App/Http/Controllers/Admin/Category/CategoryController';

const props = defineProps({
    category: {
        type: Object,
        required: true
    },
    statistics: {
        type: Object,
        default: () => ({})
    }
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Categories',
        href: CategoryController.index.url(),
    },
    {
        title: props.category.name,
        href: CategoryController.show.url(props.category.id),
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
    router.visit(CategoryController.edit.url(props.category.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this category? All subcategories will also be deleted.')) {
        router.delete(CategoryController.destroy.url(props.category.id));
    }
};

const handleToggleStatus = () => {
    router.post(CategoryController.toggleStatus.url(props.category.id), {}, {
        preserveScroll: true,
    });
};

const handleToggleFeatured = () => {
    router.post(CategoryController.toggleFeatured.url(props.category.id), {}, {
        preserveScroll: true,
    });
};

const handleViewChild = (childId) => {
    router.visit(CategoryController.show.url(childId));
};

const handleEditChild = (childId) => {
    router.visit(CategoryController.edit.url(childId));
};

const handleCreateSubcategory = () => {
    router.visit(CategoryController.create.url() + `?parent_id=${props.category.id}`);
};

const handleImageError = (event) => {
    console.error('Image failed to load:', event.target.src);
    // Optionally set a fallback image
    event.target.style.display = 'none';
};
</script>

<template>
    <Head :title="category.name" />

    <AppSidebarLayout :title="category.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div v-if="category.image" class="h-16 w-16 rounded-lg overflow-hidden bg-gray-100">
                        <img 
                            :src="category.image_url || `/storage/${category.image}`" 
                            :alt="category.name"
                            class="h-full w-full object-cover"
                            @error="handleImageError"
                        />
                    </div>
                    <div v-else class="flex h-16 w-16 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="pi pi-folder text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ category.name }}</h1>
                        <code class="text-sm bg-muted px-2 py-1 rounded">{{ category.slug }}</code>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button 
                        label="Add Subcategory" 
                        icon="pi pi-plus"
                        severity="success"
                        @click="handleCreateSubcategory"
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
                    <!-- Basic Information -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Status</label>
                                    <div class="mt-1">
                                        <Tag 
                                            :value="category.is_active ? 'Active' : 'Inactive'" 
                                            :severity="category.is_active ? 'success' : 'secondary'"
                                            class="cursor-pointer"
                                            @click="handleToggleStatus"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Featured</label>
                                    <div class="mt-1">
                                        <Tag 
                                            :value="category.is_featured ? 'Featured' : 'Not Featured'" 
                                            :severity="category.is_featured ? 'warning' : 'secondary'"
                                            class="cursor-pointer"
                                            @click="handleToggleFeatured"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div v-if="category.description">
                                <label class="text-sm text-muted-foreground">Description</label>
                                <p class="mt-1 text-sm">{{ category.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Parent Category</label>
                                    <p class="mt-1 font-medium">
                                        <template v-if="category.parent">
                                            <Button 
                                                :label="category.parent.name" 
                                                text 
                                                size="small"
                                                class="p-0"
                                                @click="handleViewChild(category.parent.id)"
                                            />
                                        </template>
                                        <span v-else class="text-muted-foreground">Root Category</span>
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Full Path</label>
                                    <p class="mt-1 text-sm font-medium">{{ category.full_path }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Sort Order</label>
                                    <p class="mt-1 font-mono">{{ category.sort_order }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Depth Level</label>
                                    <p class="mt-1">{{ category.depth || 0 }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">ID</label>
                                    <p class="mt-1 font-mono">#{{ category.id }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Information -->
                    <div v-if="category.meta_title || category.meta_description || category.meta_keywords" 
                         class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">SEO Information</h2>
                        
                        <div class="space-y-4">
                            <div v-if="category.meta_title">
                                <label class="text-sm text-muted-foreground">Meta Title</label>
                                <p class="mt-1 font-medium">{{ category.meta_title }}</p>
                            </div>
                            <div v-if="category.meta_description">
                                <label class="text-sm text-muted-foreground">Meta Description</label>
                                <p class="mt-1 text-sm">{{ category.meta_description }}</p>
                            </div>
                            <div v-if="category.meta_keywords">
                                <label class="text-sm text-muted-foreground">Meta Keywords</label>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <Tag 
                                        v-for="keyword in category.meta_keywords.split(',')" 
                                        :key="keyword"
                                        :value="keyword.trim()" 
                                        severity="info"
                                        class="text-xs"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subcategories -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold">Subcategories</h2>
                            <Button 
                                label="Add Subcategory" 
                                icon="pi pi-plus"
                                size="small"
                                @click="handleCreateSubcategory"
                            />
                        </div>
                        
                        <div v-if="!category.children || category.children.length === 0" 
                             class="text-center py-8 text-muted-foreground">
                            <i class="pi pi-inbox text-3xl mb-2"></i>
                            <p>No subcategories found</p>
                        </div>

                        <DataTable 
                            v-else
                            :value="category.children" 
                            class="p-datatable-sm"
                            stripedRows
                            dataKey="id"
                        >
                            <Column field="name" header="Name">
                                <template #body="slotProps">
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-folder text-muted-foreground"></i>
                                        <span class="font-medium">{{ slotProps.data.name }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column field="is_active" header="Status" style="width: 100px">
                                <template #body="slotProps">
                                    <Tag 
                                        :value="slotProps.data.is_active ? 'Active' : 'Inactive'" 
                                        :severity="slotProps.data.is_active ? 'success' : 'secondary'"
                                    />
                                </template>
                            </Column>
                            <Column field="sort_order" header="Order" style="width: 80px" />
                            <Column header="Actions" style="width: 100px">
                                <template #body="slotProps">
                                    <div class="flex gap-1">
                                        <Button 
                                            icon="pi pi-eye" 
                                            severity="secondary"
                                            size="small"
                                            text
                                            rounded
                                            @click="handleViewChild(slotProps.data.id)"
                                        />
                                        <Button 
                                            icon="pi pi-pencil" 
                                            severity="info"
                                            size="small"
                                            text
                                            rounded
                                            @click="handleEditChild(slotProps.data.id)"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Statistics -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Statistics</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">Direct Children</span>
                                <span class="font-semibold">{{ statistics.children_count || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">Total Descendants</span>
                                <span class="font-semibold">{{ statistics.descendants_count || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Category Image -->
                    <div v-if="category.image" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Category Image</h2>
                        <img 
                            :src="category.image_url || `/storage/${category.image}`" 
                            :alt="category.name"
                            class="w-full rounded-lg object-cover"
                            @error="handleImageError"
                        />
                    </div>

                    <!-- Timestamps -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Timestamps</h2>
                        
                        <div class="space-y-3 text-sm">
                            <div>
                                <label class="text-muted-foreground">Created</label>
                                <p class="font-medium">{{ formatDate(category.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-muted-foreground">Last Updated</label>
                                <p class="font-medium">{{ formatDate(category.updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                        
                        <div class="space-y-2">
                            <Button 
                                :label="category.is_active ? 'Deactivate' : 'Activate'"
                                :icon="category.is_active ? 'pi pi-times-circle' : 'pi pi-check-circle'"
                                :severity="category.is_active ? 'secondary' : 'success'"
                                class="w-full"
                                @click="handleToggleStatus"
                            />
                            <Button 
                                :label="category.is_featured ? 'Remove Featured' : 'Mark Featured'"
                                :icon="category.is_featured ? 'pi pi-star' : 'pi pi-star-fill'"
                                :severity="category.is_featured ? 'secondary' : 'warning'"
                                class="w-full"
                                @click="handleToggleFeatured"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

