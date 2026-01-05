<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputSwitch from 'primevue/inputswitch';
import { computed } from 'vue';
import TagController from '@/actions/App/Http/Controllers/Admin/Tag/TagController';

const props = defineProps({
    tag: {
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
        title: 'Tags',
        href: TagController.index.url(),
    },
    {
        title: props.tag.name,
        href: TagController.show.url(props.tag.id),
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
    router.visit(TagController.edit.url(props.tag.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this tag?')) {
        router.delete(TagController.destroy.url(props.tag.id));
    }
};

const handleToggleStatus = () => {
    router.post(TagController.toggleStatus.url(props.tag.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="tag.name" />

    <AppSidebarLayout :title="tag.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ tag.name }}</h1>
                    <code class="text-sm bg-muted px-2 py-1 rounded mt-1">{{ tag.slug }}</code>
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
                                <label class="text-sm text-muted-foreground">Tag Name</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <span 
                                        v-if="tag.color" 
                                        class="w-6 h-6 rounded border"
                                        :style="{ backgroundColor: tag.color }"
                                        :title="tag.color"
                                    ></span>
                                    <p class="font-medium text-lg">{{ tag.name }}</p>
                                </div>
                            </div>

                            <div v-if="tag.description">
                                <label class="text-sm text-muted-foreground">Description</label>
                                <p class="mt-1 text-sm">{{ tag.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">ID</label>
                                    <p class="mt-1 font-mono">#{{ tag.id }}</p>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Sort Order</label>
                                    <p class="mt-1 font-mono">{{ tag.sort_order }}</p>
                                </div>
                            </div>

                            <div v-if="tag.color">
                                <label class="text-sm text-muted-foreground">Color</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <span 
                                        class="w-8 h-8 rounded border"
                                        :style="{ backgroundColor: tag.color }"
                                    ></span>
                                    <code class="text-sm">{{ tag.color }}</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Settings</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-muted-foreground">Status</label>
                                <div class="mt-1">
                                    <InputSwitch 
                                        :modelValue="tag.is_active" 
                                        @update:modelValue="handleToggleStatus"
                                    />
                                    <span class="ml-2 text-sm">
                                        {{ tag.is_active ? 'Active' : 'Inactive' }}
                                    </span>
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
                                <p class="font-medium">{{ formatDate(tag.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-muted-foreground">Last Updated</label>
                                <p class="font-medium">{{ formatDate(tag.updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                        
                        <div class="space-y-2">
                            <Button 
                                :label="tag.is_active ? 'Deactivate' : 'Activate'"
                                :icon="tag.is_active ? 'pi pi-times-circle' : 'pi pi-check-circle'"
                                :severity="tag.is_active ? 'secondary' : 'success'"
                                class="w-full"
                                @click="handleToggleStatus"
                            />
                            <Button 
                                label="Edit Tag"
                                icon="pi pi-pencil"
                                severity="info"
                                class="w-full"
                                @click="handleEdit"
                            />
                            <Button 
                                label="Delete Tag"
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

