<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { computed } from 'vue';
import ScentFamilyController from '@/actions/App/Http/Controllers/Admin/ScentFamily/ScentFamilyController';

const props = defineProps({
    scentFamily: {
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
        title: 'Scent Families',
        href: ScentFamilyController.index.url(),
    },
    {
        title: props.scentFamily.name,
        href: ScentFamilyController.show.url(props.scentFamily.id),
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
    router.visit(ScentFamilyController.edit.url(props.scentFamily.id));
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this scent family?')) {
        router.delete(ScentFamilyController.destroy.url(props.scentFamily.id));
    }
};
</script>

<template>
    <Head :title="scentFamily.name" />

    <AppSidebarLayout :title="scentFamily.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ scentFamily.name }}</h1>
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
                                <label class="text-sm text-muted-foreground">Scent Family Name</label>
                                <p class="mt-1 font-medium text-lg">{{ scentFamily.name }}</p>
                            </div>

                            <div>
                                <label class="text-sm text-muted-foreground">Status</label>
                                <p class="mt-1">
                                    <span :class="scentFamily.status ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                        {{ scentFamily.status ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm text-muted-foreground">ID</label>
                                    <p class="mt-1 font-mono">#{{ scentFamily.id }}</p>
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
                                <p class="font-medium">{{ formatDate(scentFamily.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-muted-foreground">Last Updated</label>
                                <p class="font-medium">{{ formatDate(scentFamily.updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="rounded-lg border border-border bg-card p-6">
                        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                        
                        <div class="space-y-2">
                            <Button 
                                label="Edit Scent Family"
                                icon="pi pi-pencil"
                                severity="info"
                                class="w-full"
                                @click="handleEdit"
                            />
                            <Button 
                                label="Delete Scent Family"
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

