<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import InputSwitch from 'primevue/inputswitch';
import InputNumber from 'primevue/inputnumber';
import FileUpload from 'primevue/fileupload';
import { computed, ref } from 'vue';
import CategoryController from '@/actions/App/Http/Controllers/Admin/Category/CategoryController';

const props = defineProps({
    parentCategories: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    name: '',
    slug: '',
    description: '',
    parent_id: null,
    is_active: true,
    is_featured: false,
    sort_order: 0,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    image: null,
});

const imagePreview = ref(null);
const showSeoFields = ref(false);

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
        title: 'Create Category',
        href: CategoryController.create.url(),
    },
]);

// Transform categories for Dropdown
const parentOptions = computed(() => {
    return [
        { id: null, name: '— None (Root Category) —' },
        ...props.parentCategories
    ];
});

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

const handleImageRemove = () => {
    form.image = null;
    imagePreview.value = null;
};

const generateSlug = () => {
    if (form.name) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
};

function save() {
    form.post(CategoryController.store.url(), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            imagePreview.value = null;
        }
    });
}

function cancel() {
    router.visit(CategoryController.index.url());
}
</script>

<template>

    <Head title="Create Category" />

    <AppSidebarLayout title="Create Category" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-sm font-medium">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <InputText id="name" v-model="form.name" placeholder="Enter category name..."
                                    @blur="generateSlug" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="slug" class="text-sm font-medium">
                                    Slug
                                    <!-- <Button type="button" icon="pi pi-refresh" size="small" text class="ml-1"
                                        @click="generateSlug" v-tooltip.top="'Generate from name'" /> -->
                                </label>
                                <InputText id="slug" v-model="form.slug" placeholder="category-slug" />
                                <small class="text-muted-foreground">Leave empty to auto-generate</small>
                                <InputError :message="form.errors.slug" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="text-sm font-medium">Description</label>
                            <Textarea id="description" v-model="form.description"
                                placeholder="Enter category description..." rows="4"
                                :class="{ 'p-invalid': form.errors.description }" />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="parent_id" class="text-sm font-medium">Parent Category</label>
                            <Dropdown id="parent_id" v-model="form.parent_id" :options="parentOptions"
                                optionLabel="name" optionValue="id" placeholder="Select parent category..." showClear
                                class="w-full" />
                            <small class="text-muted-foreground">Leave empty to create a root category</small>
                            <InputError :message="form.errors.parent_id" />
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Category Image</h3>

                        <div class="flex flex-col gap-4">
                            <div v-if="imagePreview" class="relative inline-block">
                                <img :src="imagePreview" alt="Preview"
                                    class="h-32 w-32 object-cover rounded-lg border" />
                                <Button type="button" icon="pi pi-times" severity="danger" size="small" rounded
                                    class="absolute -top-2 -right-2" @click="handleImageRemove" />
                            </div>

                            <FileUpload mode="basic" accept="image/*" :maxFileSize="2000000" chooseLabel="Choose Image"
                                :auto="false" @select="handleImageSelect" />
                            <small class="text-muted-foreground">Accepted formats: JPEG, PNG, GIF, WebP. Max size:
                                2MB</small>
                            <InputError :message="form.errors.image" />
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Settings</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.is_active" inputId="is_active" />
                                <label for="is_active" class="text-sm font-medium cursor-pointer">
                                    Active
                                </label>
                            </div>

                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.is_featured" inputId="is_featured" />
                                <label for="is_featured" class="text-sm font-medium cursor-pointer">
                                    Featured
                                </label>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="sort_order" class="text-sm font-medium">Sort Order</label>
                                <InputNumber id="sort_order" v-model="form.sort_order" :min="0" />
                                <InputError :message="form.errors.sort_order" />
                            </div>
                        </div>
                    </div>

                    <!-- SEO Fields (Collapsible) -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 cursor-pointer border-b pb-2"
                            @click="showSeoFields = !showSeoFields">
                            <i :class="showSeoFields ? 'pi pi-chevron-down' : 'pi pi-chevron-right'"></i>
                            <h3 class="text-lg font-semibold">SEO Settings</h3>
                            <span class="text-sm text-muted-foreground">(optional)</span>
                        </div>

                        <div v-show="showSeoFields" class="space-y-4">
                            <div class="flex flex-col gap-2">
                                <label for="meta_title" class="text-sm font-medium">Meta Title</label>
                                <InputText id="meta_title" v-model="form.meta_title" placeholder="SEO title..." />
                                <small class="text-muted-foreground">
                                    {{ form.meta_title?.length || 0 }}/255 characters
                                </small>
                                <InputError :message="form.errors.meta_title" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="meta_description" class="text-sm font-medium">Meta Description</label>
                                <Textarea id="meta_description" v-model="form.meta_description"
                                    placeholder="SEO description..." rows="3"
                                    :class="{ 'p-invalid': form.errors.meta_description }" />
                                <small class="text-muted-foreground">
                                    {{ form.meta_description?.length || 0 }}/500 characters
                                </small>
                                <InputError :message="form.errors.meta_description" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="meta_keywords" class="text-sm font-medium">Meta Keywords</label>
                                <InputText id="meta_keywords" v-model="form.meta_keywords"
                                    placeholder="keyword1, keyword2, keyword3..." />
                                <InputError :message="form.errors.meta_keywords" />
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-2 mt-4 pt-4 border-t">
                        <Button type="button" label="Cancel" severity="secondary" outlined @click="cancel" />
                        <Button type="submit" label="Create Category" icon="pi pi-check" :loading="form.processing"
                            :disabled="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
