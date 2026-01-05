<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputSwitch from 'primevue/inputswitch';
import { computed } from 'vue';
import AttributeController from '@/actions/App/Http/Controllers/Admin/Attribute/AttributeController';

const props = defineProps({
    attribute: {
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
        title: 'Attributes',
        href: AttributeController.index.url(),
    },
    {
        title: props.attribute.name,
        href: AttributeController.show.url(props.attribute.id),
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
    router.visit(AttributeController.edit.url(props.attribute.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this attribute?')) {
        router.delete(AttributeController.destroy.url(props.attribute.id));
    }
};

const handleToggleStatus = () => {
    router.post(AttributeController.toggleStatus.url(props.attribute.id), {}, {
        preserveScroll: true,
    });
};

const handleToggleFilterable = () => {
    router.post(AttributeController.toggleFilterable.url(props.attribute.id), {}, {
        preserveScroll: true,
    });
};

const getTypeSeverity = (type) => {
    const severityMap = {
        'text': 'info',
        'number': 'warning',
        'select': 'success',
        'multiselect': 'success',
        'boolean': 'secondary',
        'date': 'help',
        'textarea': 'info',
    };
    return severityMap[type] || 'secondary';
};
</script>

<template>

    <Head :title="attribute.name" />

    <AppSidebarLayout :title="attribute.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ attribute.name }}</h1>
                    <code class="text-sm bg-muted px-2 py-1 rounded mt-1">{{ attribute.slug }}</code>
                </div>
                <div class="flex gap-2">
                    <Button label="Edit" icon="pi pi-pencil" severity="info" @click="handleEdit" />
                    <Button label="Delete" icon="pi pi-trash" severity="danger" outlined @click="handleDelete" />
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
                                <label class="text-sm text-muted-foreground">Attribute Name</label>
                                <p class="mt-1 font-medium text-lg">{{ attribute.name }}</p>
                            </div>

                            <div v-if="attribute.description">
                                <label class="text-sm text-muted-foreground">Description</label>
                                <p class="mt-1 text-sm">{{ attribute.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Type</label>
                                    <div class="mt-1">
                                        <Tag :value="attribute.type" :severity="getTypeSeverity(attribute.type)" />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Slug</label>
                                    <p class="mt-1 font-mono">{{ attribute.slug }}</p>
                                </div>
                            </div>

                            <div v-if="attribute.default_value">
                                <label class="text-sm text-muted-foreground">Default Value</label>
                                <p class="mt-1 font-medium">{{ attribute.default_value }}</p>
                            </div>

                            <div>
                                <label class="text-sm text-muted-foreground">Attribute Values</label>
                                <div v-if="attribute.values && attribute.values.length > 0" class="mt-1 space-y-2">
                                    <div v-for="(value, index) in attribute.values" :key="value.id || index"
                                        class="flex items-center gap-2 p-2 border rounded">
                                        <span class="font-medium">{{ value.value }}</span>
                                        <span v-if="value.display_value" class="text-sm text-gray-500">
                                            ({{ value.display_value }})
                                        </span>
                                        <span v-if="value.color_code" class="w-6 h-6 rounded border"
                                            :style="{ backgroundColor: value.color_code }"
                                            :title="value.color_code"></span>
                                        <Tag :value="value.is_active ? 'Active' : 'Inactive'"
                                            :severity="value.is_active ? 'success' : 'secondary'"
                                            class="text-xs ml-auto" />
                                    </div>
                                </div>
                                <div v-else class="mt-1 text-sm text-gray-500 italic">
                                    No attribute values defined.
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
                                        <InputSwitch :modelValue="attribute.is_active"
                                            @update:modelValue="handleToggleStatus" />
                                        <span class="ml-2 text-sm">
                                            {{ attribute.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Filterable</label>
                                    <div class="mt-1">
                                        <InputSwitch :modelValue="attribute.is_filterable"
                                            @update:modelValue="handleToggleFilterable" />
                                        <span class="ml-2 text-sm">
                                            {{ attribute.is_filterable ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">Required</label>
                                    <div class="mt-1">
                                        <Tag :value="attribute.is_required ? 'Yes' : 'No'"
                                            :severity="attribute.is_required ? 'danger' : 'secondary'" />
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm text-muted-foreground">Searchable</label>
                                    <div class="mt-1">
                                        <Tag :value="attribute.is_searchable ? 'Yes' : 'No'"
                                            :severity="attribute.is_searchable ? 'success' : 'secondary'" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm text-muted-foreground">Sort Order</label>
                                <p class="mt-1 font-mono">{{ attribute.sort_order }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Timestamps -->
                    <!-- <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Timestamps</h2>

                        <div class="space-y-3 text-sm">
                            <div>
                                <label class="text-muted-foreground">Created</label>
                                <p class="font-medium">{{ formatDate(attribute.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-muted-foreground">Last Updated</label>
                                <p class="font-medium">{{ formatDate(attribute.updated_at) }}</p>
                            </div>
                        </div>
                    </div> -->

                    <!-- Quick Actions -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>

                        <div class="space-y-2">
                            <Button :label="attribute.is_active ? 'Deactivate' : 'Activate'"
                                :icon="attribute.is_active ? 'pi pi-times-circle' : 'pi pi-check-circle'"
                                :severity="attribute.is_active ? 'secondary' : 'success'" class="w-full"
                                @click="handleToggleStatus" />
                            <Button :label="attribute.is_filterable ? 'Disable Filterable' : 'Enable Filterable'"
                                :icon="attribute.is_filterable ? 'pi pi-filter-slash' : 'pi pi-filter'"
                                :severity="attribute.is_filterable ? 'secondary' : 'info'" class="w-full"
                                @click="handleToggleFilterable" />
                            <Button label="Edit Attribute" icon="pi pi-pencil" severity="info" class="w-full"
                                @click="handleEdit" />
                            <Button label="Delete Attribute" icon="pi pi-trash" severity="danger" outlined
                                class="w-full" @click="handleDelete" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
