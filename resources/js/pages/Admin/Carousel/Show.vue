<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import Button from 'primevue/button';
import { computed } from 'vue';
import CarouselController from '@/actions/App/Http/Controllers/Admin/Carousel/CarouselController';

const props = defineProps({
    carousel: {
        type: Object,
        required: true,
    },
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Carousels',
        href: CarouselController.index.url(),
    },
    {
        title: props.carousel.title,
        href: CarouselController.show.url(props.carousel.id),
    },
]);

const handleEdit = () => {
    router.visit(CarouselController.edit.url(props.carousel.id));
};

const handleBack = () => {
    router.visit(CarouselController.index.url());
};
</script>

<template>
    <Head :title="`View ${carousel.title}`" />

    <AppSidebarLayout :title="`View ${carousel.title}`" :breadcrumbs="breadcrumbItems">
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column - Details -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Carousel Details</h3>
                    
                    <!-- Title -->
                    <div class="flex flex-col gap-2 mb-6">
                        <label class="font-medium text-gray-700">Title</label>
                        <p class="text-gray-900">{{ carousel.title }}</p>
                    </div>

                    <!-- Subtitle -->
                    <div class="flex flex-col gap-2 mb-6">
                        <label class="font-medium text-gray-700">Subtitle</label>
                        <p v-if="carousel.subtitle" class="text-gray-900">{{ carousel.subtitle }}</p>
                        <p v-else class="text-gray-400">-</p>
                    </div>

                    <!-- Button Name -->
                    <div class="flex flex-col gap-2 mb-6">
                        <label class="font-medium text-gray-700">Button Name</label>
                        <p v-if="carousel.button_name" class="text-gray-900">{{ carousel.button_name }}</p>
                        <p v-else class="text-gray-400">-</p>
                    </div>

                    <!-- Button URL -->
                    <div class="flex flex-col gap-2 mb-6">
                        <label class="font-medium text-gray-700">Button URL</label>
                        <p v-if="carousel.button_url" class="text-gray-900">{{ carousel.button_url }}</p>
                        <p v-else class="text-gray-400">-</p>
                    </div>

                    <!-- Image -->
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-gray-700">Image</label>
                        <p class="text-gray-900 break-all">{{ carousel.image }}</p>
                    </div>
                </div>

                <!-- Right Column - Status & Actions -->
                <div class="flex flex-col gap-6">
                    <!-- Status Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status</h3>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <label class="font-medium text-gray-700">Status</label>
                            <span v-if="carousel.is_active" class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                Active
                            </span>
                            <span v-else class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                                Inactive
                            </span>
                        </div>
                    </div>

                    <!-- Timestamps Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Information</h3>
                        <div class="flex flex-col gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="text-sm text-gray-600 block mb-1">Created</label>
                                <p class="text-gray-900">{{ new Date(carousel.created_at).toLocaleString() }}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="text-sm text-gray-600 block mb-1">Last Updated</label>
                                <p class="text-gray-900">{{ new Date(carousel.updated_at).toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions</h3>
                        <div class="flex flex-col gap-3">
                            <Button 
                                label="Edit" 
                                icon="pi pi-pencil" 
                                severity="primary"
                                class="w-full"
                                @click="handleEdit"
                            />
                            <Button 
                                label="Back to List" 
                                severity="secondary" 
                                outlined
                                class="w-full"
                                @click="handleBack"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
