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
import CollectionController from '@/actions/App/Http/Controllers/Admin/Collection/CollectionController';

const props = defineProps({
    collection: {
        type: Object,
        required: true
    }
});

const form = useForm({
    name: props.collection.name || '',
    slug: props.collection.slug || '',
    description: props.collection.description || '',
    image: null,
    banner: null,
    is_active: props.collection.is_active ?? true,
    is_featured: props.collection.is_featured ?? false,
    sort_order: props.collection.sort_order || 0,
    meta_title: props.collection.meta_title || '',
    meta_description: props.collection.meta_description || '',
    meta_keywords: props.collection.meta_keywords || '',
});

const imagePreview = ref(props.collection.image_url || null);
const bannerPreview = ref(props.collection.banner_url || null);
const showSeoFields = ref(false);

// Show SEO fields if any SEO data exists
if (props.collection.meta_title || props.collection.meta_description || props.collection.meta_keywords) {
    showSeoFields.value = true;
}

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
        form.image = file;
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleBannerSelect = (event) => {
    const file = event.files[0];
    if (file) {
        form.banner = file;
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            bannerPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleImageRemove = () => {
    form.image = null;
    // If there was an existing image, keep the preview but mark for deletion
    if (props.collection.image_url) {
        imagePreview.value = props.collection.image_url;
    } else {
        imagePreview.value = null;
    }
};

const handleBannerRemove = () => {
    form.banner = null;
    // If there was an existing banner, keep the preview but mark for deletion
    if (props.collection.banner_url) {
        bannerPreview.value = props.collection.banner_url;
    } else {
        bannerPreview.value = null;
    }
};

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
        title: `Edit: ${props.collection.name}`,
        href: CollectionController.edit.url(props.collection.id),
    },
]);

function save() {
    form.post(CollectionController.update.url(props.collection.id), {
        preserveScroll: true,
        forceFormData: true,
    });
}

function cancel() {
    router.visit(CollectionController.index.url());
}
</script>

<template>
    <Head :title="`Edit: ${collection.name}`" />

    <AppSidebarLayout :title="`Edit: ${collection.name}`" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-sm font-medium">
                                    Collection Name <span class="text-red-500">*</span>
                                </label>
                                <InputText 
                                    id="name" 
                                    v-model="form.name" 
                                    placeholder="Enter collection name..." 
                                    :class="{ 'p-invalid': form.errors.name }"
                                    aria-describedby="name-help"
                                    class="w-full"
                                    @blur="generateSlug"
                                />
                                <InputError :message="form.errors.name" />
                                <small id="name-help" class="text-muted-foreground">
                                    Enter a unique collection name
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
                                placeholder="Enter collection description..." 
                                :class="{ 'p-invalid': form.errors.description }"
                                rows="3"
                                class="w-full"
                            />
                            <InputError :message="form.errors.description" />
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

                    <!-- Images -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Images</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Collection Image -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Collection Image</label>
                                <FileUpload 
                                    mode="basic"
                                    name="image"
                                    accept="image/*"
                                    :maxFileSize="2048000"
                                    @select="handleImageSelect"
                                    chooseLabel="Choose Image"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.image" />
                                <small class="text-muted-foreground">
                                    Upload collection image (Max: 2MB, Formats: JPEG, PNG, JPG, GIF, SVG)
                                </small>
                                
                                <!-- Image Preview -->
                                <div v-if="imagePreview" class="mt-4">
                                    <div class="relative inline-block">
                                        <img 
                                            :src="imagePreview" 
                                            alt="Image Preview" 
                                            class="w-32 h-32 object-contain border rounded-lg"
                                        />
                                        <button 
                                            type="button"
                                            @click="handleImageRemove"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600"
                                        >
                                            <i class="pi pi-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Banner Image -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Banner Image</label>
                                <FileUpload 
                                    mode="basic"
                                    name="banner"
                                    accept="image/*"
                                    :maxFileSize="5120000"
                                    @select="handleBannerSelect"
                                    chooseLabel="Choose Banner"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.banner" />
                                <small class="text-muted-foreground">
                                    Upload banner image (Max: 5MB, Formats: JPEG, PNG, JPG, GIF, SVG)
                                </small>
                                
                                <!-- Banner Preview -->
                                <div v-if="bannerPreview" class="mt-4">
                                    <div class="relative inline-block">
                                        <img 
                                            :src="bannerPreview" 
                                            alt="Banner Preview" 
                                            class="w-full max-w-xs h-32 object-cover border rounded-lg"
                                        />
                                        <button 
                                            type="button"
                                            @click="handleBannerRemove"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600"
                                        >
                                            <i class="pi pi-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>
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
                                    <p class="text-xs text-muted-foreground">Collection is active</p>
                                </div>
                                <InputSwitch v-model="form.is_active" />
                            </div>

                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Featured</label>
                                    <p class="text-xs text-muted-foreground">Show as featured collection</p>
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
                        <Button type="submit" label="Update Collection" icon="pi pi-check" :loading="form.processing"
                            :disabled="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

