<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import FileUpload from 'primevue/fileupload';
import { computed, ref } from 'vue';
import BrandController from '@/actions/App/Http/Controllers/Admin/Brand/BrandController';

const props = defineProps({
    brand: {
        type: Object,
        required: true
    }
});

const form = useForm({
    _method: 'PUT',
    name: props.brand.name || '',
    slug: props.brand.slug || '',
    description: props.brand.description || '',
    main_image: null,
    gallery_images: [],
    website: props.brand.website || '',
    is_active: props.brand.is_active ?? true,
    is_featured: props.brand.is_featured ?? false,
    sort_order: props.brand.sort_order || 0,
    meta_title: props.brand.meta_title || '',
    meta_description: props.brand.meta_description || '',
    meta_keywords: props.brand.meta_keywords || '',
    remove_main_image: false,
    remove_gallery_images: [],
});

const mainImagePreview = ref(props.brand.main_image_url || null);
const galleryPreviews = ref([]);
const existingGalleryImages = ref(props.brand.gallery_images_urls || []);
const showSeoFields = ref(false);

const generateSlug = () => {
    if (form.name && !form.slug) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
};

const handleImageSelect = (event) => {
    const file = event.files[0];
    if (file) {
        form.main_image = file;
        form.remove_main_image = false;
        const reader = new FileReader();
        reader.onload = (e) => {
            mainImagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleImageRemove = () => {
    form.main_image = null;
    form.remove_main_image = true;
    mainImagePreview.value = null;
};

const handleGallerySelect = (event) => {
    const files = Array.from(event.files);
    files.forEach(file => {
        if (file) {
            form.gallery_images.push(file);
            const reader = new FileReader();
            reader.onload = (e) => {
                galleryPreviews.value.push(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
};

const removeGalleryImage = (index) => {
    if (index < existingGalleryImages.value.length) {
        // Remove existing image
        form.remove_gallery_images.push(existingGalleryImages.value[index]);
        existingGalleryImages.value.splice(index, 1);
    } else {
        // Remove new image
        const newIndex = index - existingGalleryImages.value.length;
        form.gallery_images.splice(newIndex, 1);
        galleryPreviews.value.splice(newIndex, 1);
    }
};

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
        title: `Edit: ${props.brand.name}`,
        href: BrandController.edit.url(props.brand.id),
    },
]);

function save() {
    form.post(BrandController.update.url(props.brand.id), {
        preserveScroll: true,
        forceFormData: true,
    });
}

function cancel() {
    router.visit(BrandController.index.url());
}
</script>

<template>
    <Head :title="`Edit: ${brand.name}`" />

    <AppSidebarLayout :title="`Edit: ${brand.name}`" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-sm font-medium">
                                    Brand Name <span class="text-red-500">*</span>
                                </label>
                                <InputText 
                                    id="name" 
                                    v-model="form.name" 
                                    placeholder="Enter brand name..." 
                                    :class="{ 'p-invalid': form.errors.name }"
                                    aria-describedby="name-help"
                                    class="w-full"
                                    @blur="generateSlug"
                                />
                                <InputError :message="form.errors.name" />
                                <small id="name-help" class="text-muted-foreground">
                                    Enter a unique brand name
                                </small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="slug" class="text-sm font-medium">Slug</label>
                                <InputText 
                                    id="slug" 
                                    v-model="form.slug" 
                                    placeholder="Auto-generated from name" 
                                    :class="{ 'p-invalid': form.errors.slug }"
                                    aria-describedby="slug-help"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.slug" />
                                <small id="slug-help" class="text-muted-foreground">
                                    URL-friendly identifier (auto-generated if left empty)
                                </small>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="text-sm font-medium">Description</label>
                            <Textarea 
                                id="description" 
                                v-model="form.description" 
                                placeholder="Enter brand description..." 
                                :class="{ 'p-invalid': form.errors.description }"
                                rows="3"
                                class="w-full"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="website" class="text-sm font-medium">Website</label>
                                <InputText 
                                    id="website" 
                                    v-model="form.website" 
                                    placeholder="https://example.com" 
                                    :class="{ 'p-invalid': form.errors.website }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.website" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="sort_order" class="text-sm font-medium">Sort Order</label>
                                <InputText 
                                    id="sort_order" 
                                    v-model.number="form.sort_order" 
                                    type="number"
                                    placeholder="0" 
                                    :class="{ 'p-invalid': form.errors.sort_order }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.sort_order" />
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Images</h3>

                        <div class="flex flex-col gap-4">
                            <div>
                                <label class="text-sm font-medium mb-2 block">Main Image</label>
                                <div v-if="mainImagePreview" class="relative inline-block mb-2">
                                    <img 
                                        :src="mainImagePreview" 
                                        alt="Main Image Preview" 
                                        class="h-24 w-24 object-cover rounded-lg border" 
                                    />
                                    <Button type="button" icon="pi pi-times" severity="danger" size="small" rounded
                                        class="absolute -top-2 -right-2" @click="handleImageRemove" />
                                </div>
                                <FileUpload 
                                    mode="basic"
                                    name="main_image"
                                    accept="image/*"
                                    :maxFileSize="5000000"
                                    chooseLabel="Choose Main Image"
                                    :auto="false"
                                    @select="handleImageSelect"
                                    class="w-50"
                                />
                                <small class="text-muted-foreground">Max size: 5MB</small>
                                <InputError :message="form.errors.main_image" />
                            </div>

                            <div>
                                <label class="text-sm font-medium mb-2 block">Gallery Images</label>
                                <div v-if="existingGalleryImages.length > 0 || galleryPreviews.length > 0" class="flex flex-wrap gap-2 mb-2">
                                    <div v-for="(image, index) in existingGalleryImages" :key="`existing-${index}`" class="relative">
                                        <img :src="image" alt="Gallery preview"
                                            class="h-24 w-24 object-cover rounded-lg border" />
                                        <Button type="button" icon="pi pi-times" severity="danger" size="small" rounded
                                            class="absolute -top-2 -right-2" @click="removeGalleryImage(index)" />
                                    </div>
                                    <div v-for="(preview, index) in galleryPreviews" :key="`new-${index}`" class="relative">
                                        <img :src="preview" alt="Gallery preview"
                                            class="h-24 w-24 object-cover rounded-lg border" />
                                        <Button type="button" icon="pi pi-times" severity="danger" size="small" rounded
                                            class="absolute -top-2 -right-2" @click="removeGalleryImage(existingGalleryImages.length + index)" />
                                    </div>
                                </div>
                                <FileUpload 
                                    mode="basic"
                                    name="gallery"
                                    accept="image/*"
                                    :maxFileSize="5000000"
                                    chooseLabel="Add Gallery Image"
                                    :auto="false"
                                    @select="handleGallerySelect"
                                    :multiple="true"
                                    class="w-50"
                                />
                                <small class="text-muted-foreground">Max size: 5MB per image</small>
                                <InputError :message="form.errors.gallery_images" />
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Settings</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Active</label>
                                    <p class="text-xs text-muted-foreground">Brand is active</p>
                                </div>
                                <InputSwitch v-model="form.is_active" />
                            </div>

                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Featured</label>
                                    <p class="text-xs text-muted-foreground">Show as featured brand</p>
                                </div>
                                <InputSwitch v-model="form.is_featured" />
                            </div>
                        </div>
                    </div>

                    <!-- SEO Fields -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h3 class="text-lg font-semibold">SEO Settings</h3>
                            <Button 
                                :label="showSeoFields ? 'Hide SEO' : 'Show SEO'"
                                icon="pi pi-cog"
                                severity="secondary"
                                text
                                @click="showSeoFields = !showSeoFields"
                                type="button"
                            />
                        </div>
                        
                        <div v-if="showSeoFields" class="space-y-4">
                            <div class="flex flex-col gap-2">
                                <label for="meta_title" class="text-sm font-medium">Meta Title</label>
                                <InputText 
                                    id="meta_title" 
                                    v-model="form.meta_title" 
                                    placeholder="SEO meta title" 
                                    :class="{ 'p-invalid': form.errors.meta_title }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.meta_title" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="meta_description" class="text-sm font-medium">Meta Description</label>
                                <Textarea 
                                    id="meta_description" 
                                    v-model="form.meta_description" 
                                    placeholder="SEO meta description" 
                                    :class="{ 'p-invalid': form.errors.meta_description }"
                                    rows="3"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.meta_description" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="meta_keywords" class="text-sm font-medium">Meta Keywords</label>
                                <InputText 
                                    id="meta_keywords" 
                                    v-model="form.meta_keywords" 
                                    placeholder="keyword1, keyword2, keyword3" 
                                    :class="{ 'p-invalid': form.errors.meta_keywords }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.meta_keywords" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4 pt-4 border-t">
                        <Button type="button" label="Cancel" severity="secondary" outlined @click="cancel" />
                        <Button type="submit" label="Update Brand" icon="pi pi-check" :loading="form.processing"
                            :disabled="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

