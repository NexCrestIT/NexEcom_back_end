<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import { computed } from 'vue';
import CarouselController from '@/actions/App/Http/Controllers/Admin/Carousel/CarouselController';

const props = defineProps({
    carousel: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    title: props.carousel.title,
    subtitle: props.carousel.subtitle,
    button_name: props.carousel.button_name,
    button_url: props.carousel.button_url,
    image: props.carousel.image,
    is_active: props.carousel.is_active,
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
        title: 'Edit Carousel',
        href: CarouselController.edit.url(props.carousel.id),
    },
]);

const submit = () => {
    form.put(CarouselController.update.url(props.carousel.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Carousel" />

    <AppSidebarLayout title="Edit Carousel" :breadcrumbs="breadcrumbItems">
        <div class="p-6">
            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column - Form Fields -->
                <div class="bg-white rounded-lg border border-gray-200 p-6 flex flex-col gap-6">
                    <!-- Title -->
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                        <InputText 
                            v-model="form.title" 
                            placeholder="Enter main heading"
                            :class="{ 'ng-invalid ng-touched': form.errors.title }"
                        />
                        <span v-if="form.errors.title" class="text-red-500 text-sm">{{ form.errors.title }}</span>
                    </div>

                    <!-- Subtitle -->
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-gray-700">Subtitle</label>
                        <InputText 
                            v-model="form.subtitle" 
                            placeholder="Enter subheading"
                        />
                        <span v-if="form.errors.subtitle" class="text-red-500 text-sm">{{ form.errors.subtitle }}</span>
                    </div>

                    <!-- Button Name -->
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-gray-700">Button Name</label>
                        <InputText 
                            v-model="form.button_name" 
                            placeholder="e.g., Shop Now, Learn More"
                        />
                        <span v-if="form.errors.button_name" class="text-red-500 text-sm">{{ form.errors.button_name }}</span>
                    </div>

                    <!-- Button URL -->
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-gray-700">Button URL</label>
                        <InputText 
                            v-model="form.button_url" 
                            placeholder="e.g., /shop/new-collection"
                        />
                        <span v-if="form.errors.button_url" class="text-red-500 text-sm">{{ form.errors.button_url }}</span>
                    </div>

                    <!-- Image -->
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-gray-700">Image <span class="text-red-500">*</span></label>
                        <InputText 
                            v-model="form.image" 
                            placeholder="e.g., /images/carousel/summer-2025.jpg"
                            :class="{ 'ng-invalid ng-touched': form.errors.image }"
                        />
                        <span v-if="form.errors.image" class="text-red-500 text-sm">{{ form.errors.image }}</span>
                    </div>
                </div>

                <!-- Right Column - Status & Actions -->
                <div class="flex flex-col gap-6">
                    <!-- Status Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status</h3>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <label class="font-medium text-gray-700">Active</label>
                            <InputSwitch v-model="form.is_active" />
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions</h3>
                        <div class="flex flex-col gap-3">
                            <Button 
                                type="submit" 
                                label="Update Carousel" 
                                icon="pi pi-check" 
                                severity="primary"
                                :loading="form.processing"
                                class="w-full"
                            />
                            <Button 
                                type="button"
                                label="Cancel" 
                                severity="secondary" 
                                outlined
                                class="w-full"
                                @click="$inertia.visit(CarouselController.show.url(carousel.id))"
                            />
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppSidebarLayout>
</template>

<style scoped>
:deep(.p-inputtext),
:deep(.p-inputtextarea) {
    width: 100%;
}

:deep(.ng-invalid.ng-touched) {
    border-color: #f87171;
}
</style>
