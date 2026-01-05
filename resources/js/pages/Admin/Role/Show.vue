<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import { computed } from 'vue';
import { route } from '@/composables/useRouter';

const props = defineProps({
    role: {
        type: Object,
        default: () => ({})
    },
    permissions: {
        type: Object,
        default: () => ({})
    }
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Roles',
        href: route('admin.roles.index'),
    },
    {
        title: 'Role Details',
        href: route('admin.roles.show', props.role?.id),
    },
]);

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Group role permissions by module
const rolePermissionsByModule = computed(() => {
    const grouped = {};
    if (props.role?.permissions) {
        props.role.permissions.forEach(permission => {
            const module = permission.module || 'Other';
            if (!grouped[module]) {
                grouped[module] = [];
            }
            grouped[module].push(permission);
        });
    }
    return grouped;
});

const handleEdit = () => {
    router.visit(route('admin.roles.edit', props.role.id));
};
</script>

<template>
    <Head title="Role Details" />

    <AppSidebarLayout title="Role Details" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">{{ role.name }}</h2>
                    <Button 
                        label="Edit Role" 
                        icon="pi pi-pencil"
                        @click="handleEdit"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Role Name</label>
                        <p class="text-lg font-semibold mt-1">{{ role.name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Guard Name</label>
                        <p class="text-lg font-semibold mt-1">{{ role.guard_name || 'web' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Created At</label>
                        <p class="text-lg mt-1">{{ formatDate(role.created_at) }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Updated At</label>
                        <p class="text-lg mt-1">{{ formatDate(role.updated_at) }}</p>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Permissions</h3>
                        <Tag 
                            :value="`${role.permissions?.length || 0} Permissions`" 
                            severity="info"
                        />
                    </div>

                    <div v-if="!role.permissions || role.permissions.length === 0" class="text-center py-8 text-muted-foreground">
                        No permissions assigned to this role
                    </div>

                    <div v-else class="space-y-4">
                        <div 
                            v-for="(permissions, module) in rolePermissionsByModule" 
                            :key="module"
                            class="rounded-lg border border-border p-4"
                        >
                            <h4 class="font-semibold text-base mb-3">{{ module }}</h4>
                            <div class="flex flex-wrap gap-2">
                                <Tag 
                                    v-for="permission in permissions" 
                                    :key="permission.id"
                                    :value="permission.name" 
                                    severity="success"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

