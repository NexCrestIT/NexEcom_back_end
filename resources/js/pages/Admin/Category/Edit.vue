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
import { computed, ref, onMounted } from 'vue';
import CategoryController from '@/actions/App/Http/Controllers/Admin/Category/CategoryController';

const props = defineProps({
    category: {
        type: Object,
        required: true
    },
    parentCategories: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    _method: 'PUT',
    name: props.category.name || '',
    slug: props.category.slug || '',
    description: props.category.description || '',
    parent_id: props.category.parent_id || null,
    is_active: props.category.is_active ?? true,
    is_featured: props.category.is_featured ?? false,
    sort_order: props.category.sort_order || 0,
    meta_title: props.category.meta_title || '',
    meta_description: props.category.meta_description || '',
    meta_keywords: props.category.meta_keywords || '',
    image: null,
    remove_image: false,
});

const imagePreview = ref(null);
const showSeoFields = ref(false);
const currentImage = ref(props.category.image);

onMounted(() => {
    if (props.category.image) {
        imagePreview.value = props.category.image_url || `/storage/${props.category.image}`;
    }
    // Show SEO fields if any SEO data exists
    if (props.category.meta_title || props.category.meta_description || props.category.meta_keywords) {
        showSeoFields.value = true;
    }
});

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
        title: props.category.name,
        href: CategoryController.edit.url(props.category.id),
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
        form.remove_image = false;
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
    form.remove_image = true;
    imagePreview.value = null;
    currentImage.value = null;
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
    form.post(CategoryController.update.url(props.category.id), {
        preserveScroll: true,
        forceFormData: true,
    });
}

function cancel() {
    router.visit(CategoryController.index.url());
}

function deleteCategory() {
    if (confirm('Are you sure you want to delete this category? All subcategories will also be deleted.')) {
        router.delete(CategoryController.destroy.url(props.category.id));
    }
}
</script>

<template>

    <Head :title="`Edit ${category.name}`" />

    <AppSidebarLayout :title="`Edit: ${category.name}`" :breadcrumbs="breadcrumbItems">
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
                                <InputText id="name" v-model="form.name" placeholder="Enter category name..." />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="slug" class="text-sm font-medium">
                                    Slug
                                    <!-- <Button type="button" icon="pi pi-refresh" size="small" text class="ml-1"
                                        @click="generateSlug" v-tooltip.top="'Regenerate from name'" /> -->
                                </label>
                                <InputText id="slug" v-model="form.slug" placeholder="category-slug" />
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
                            <small class="text-muted-foreground">Leave empty to make this a root category</small>
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

                            <FileUpload mode="basic" accept="image/*" :maxFileSize="2000000"
                                :chooseLabel="imagePreview ? 'Change Image' : 'Choose Image'" :auto="false"
                                @select="handleImageSelect" />
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
                                    placeholder="SEO description..." rows="3" />
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

                    <!-- Category Info -->
                    <div class="space-y-2 p-4 bg-muted/50 rounded-lg">
                        <h4 class="text-sm font-medium text-muted-foreground">Category Information</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-muted-foreground">ID:</span>
                                <span class="ml-1 font-medium">{{ category.id }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Depth:</span>
                                <span class="ml-1 font-medium">{{ category.depth || 0 }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Children:</span>
                                <span class="ml-1 font-medium">{{ category.children?.length || 0 }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Path:</span>
                                <span class="ml-1 font-medium">{{ category.full_path }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between gap-2 mt-4 pt-4 border-t">
                        <Button type="button" label="Delete Category" icon="pi pi-trash" severity="danger" outlined
                            @click="deleteCategory" />
                        <div class="flex gap-2">
                            <Button type="button" label="Cancel" severity="secondary" outlined @click="cancel" />
                            <Button type="submit" label="Update Category" icon="pi pi-check" :loading="form.processing"
                                :disabled="form.processing" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
