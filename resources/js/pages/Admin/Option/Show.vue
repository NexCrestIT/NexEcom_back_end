<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputSwitch from 'primevue/inputswitch';
import Tag from 'primevue/tag';
import { computed } from 'vue';
import OptionController from '@/actions/App/Http/Controllers/Admin/Option/OptionController';

const props = defineProps({
    option: {
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
        title: 'Options',
        href: OptionController.index.url(),
    },
    {
        title: props.option.name,
        href: OptionController.show.url(props.option.id),
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
    router.visit(OptionController.edit.url(props.option.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this option?')) {
        router.delete(OptionController.destroy.url(props.option.id));
    }
};

const handleToggleStatus = () => {
    router.post(OptionController.toggleStatus.url(props.option.id), {}, {
        preserveScroll: true,
    });
};

const handleToggleRequired = () => {
    router.post(OptionController.toggleRequired.url(props.option.id), {}, {
        preserveScroll: true,
    });
};

const getTypeSeverity = (type) => {
    const severityMap = {
        'text': 'info',
        'select': 'success',
        'multiselect': 'warning',
        'radio': 'help',
        'checkbox': 'secondary',
    };
    return severityMap[type] || 'secondary';
};

const parseValue = () => {
    if (!props.option.value) return null;
    try {
        const parsed = typeof props.option.value === 'string' ? JSON.parse(props.option.value) : props.option.value;
        return Array.isArray(parsed) ? parsed : null;
    } catch (e) {
        return null;
    }
};

const optionValues = computed(() => parseValue());
</script>

<template>
    <Head :title="option.name" />

    <AppSidebarLayout :title="option.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ option.name }}</h1>
                    <code class="text-sm bg-muted px-2 py-1 rounded mt-1">{{ option.slug }}</code>
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
                                <label class="text-sm text-muted-foreground">Option Name</label>
                                <p class="mt-1 font-medium text-lg">{{ option.name }}</p>
                            </div>

                            <div v-if="option.description">
                                <label class="text-sm text-muted-foreground">Description</label>
                                <p class="mt-1 text-sm">{{ option.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Type</label>
                                    <div class="mt-1">
                                        <Tag :value="option.type" :severity="getTypeSeverity(option.type)" />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">ID</label>
                                    <p class="mt-1 font-mono">#{{ option.id }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Sort Order</label>
                                    <p class="mt-1 font-mono">{{ option.sort_order }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Option Values -->
                    <div v-if="optionValues && optionValues.length > 0" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Option Values</h2>
                        
                        <div class="space-y-2">
                            <div v-for="(item, index) in optionValues" :key="index" class="flex items-center gap-2 p-2 border rounded">
                                <span class="font-medium">{{ item.value || item }}</span>
                                <span v-if="item.label && item.label !== item.value" class="text-sm text-muted-foreground">
                                    ({{ item.label }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Default Value (for text type) -->
                    <div v-if="!optionValues && option.value" class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Default Value</h2>
                        <p class="text-sm">{{ option.value }}</p>
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
                                            :modelValue="option.is_active" 
                                            @update:modelValue="handleToggleStatus"
                                        />
                                        <span class="ml-2 text-sm">
                                            {{ option.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Required</label>
                                    <div class="mt-1">
                                        <InputSwitch 
                                            :modelValue="option.is_required" 
                                            @update:modelValue="handleToggleRequired"
                                        />
                                        <span class="ml-2 text-sm">
                                            {{ option.is_required ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Information -->
                    <div v-if="option.meta_title || option.meta_description || option.meta_keywords" 
                         class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">SEO Information</h2>
                        
                        <div class="space-y-4">
                            <div v-if="option.meta_title">
                                <label class="text-sm text-muted-foreground">Meta Title</label>
                                <p class="mt-1 text-sm">{{ option.meta_title }}</p>
                            </div>

                            <div v-if="option.meta_description">
                                <label class="text-sm text-muted-foreground">Meta Description</label>
                                <p class="mt-1 text-sm">{{ option.meta_description }}</p>
                            </div>

                            <div v-if="option.meta_keywords">
                                <label class="text-sm text-muted-foreground">Meta Keywords</label>
                                <p class="mt-1 text-sm">{{ option.meta_keywords }}</p>
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
                                <p class="font-medium">{{ formatDate(option.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-muted-foreground">Last Updated</label>
                                <p class="font-medium">{{ formatDate(option.updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                        
                        <div class="space-y-2">
                            <Button 
                                :label="option.is_active ? 'Deactivate' : 'Activate'"
                                :icon="option.is_active ? 'pi pi-times-circle' : 'pi pi-check-circle'"
                                :severity="option.is_active ? 'secondary' : 'success'"
                                class="w-full"
                                @click="handleToggleStatus"
                            />
                            <Button 
                                :label="option.is_required ? 'Make Optional' : 'Make Required'"
                                :icon="option.is_required ? 'pi pi-lock-open' : 'pi pi-lock'"
                                :severity="option.is_required ? 'warning' : 'secondary'"
                                class="w-full"
                                @click="handleToggleRequired"
                            />
                            <Button 
                                label="Edit Option"
                                icon="pi pi-pencil"
                                severity="info"
                                class="w-full"
                                @click="handleEdit"
                            />
                            <Button 
                                label="Delete Option"
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

