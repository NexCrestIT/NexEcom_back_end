<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import FileUpload from 'primevue/fileupload';
import { computed, ref } from 'vue';
import FlashSaleController from '@/actions/App/Http/Controllers/Admin/FlashSale/FlashSaleController';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    name: '',
    slug: '',
    description: '',
    start_date: null,
    end_date: null,
    banner_image: null,
    discount_type: 'percentage',
    discount_value: null,
    max_products: null,
    is_active: true,
    is_featured: false,
    sort_order: 0,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    products: [],
});

const bannerPreview = ref(null);
const showSeoFields = ref(false);
const flashSaleProducts = ref([]);

const typeOptions = [
    { label: 'Percentage', value: 'percentage' },
    { label: 'Fixed Amount', value: 'fixed' },
];

const maxValue = computed(() => {
    return form.discount_type === 'percentage' ? 100 : null;
});

const productOptions = computed(() => {
    return props.products.map(product => ({
        id: product.id,
        name: `${product.name} (${product.sku}) - $${product.price}`,
    }));
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Flash Sales',
        href: FlashSaleController.index.url(),
    },
    {
        title: 'Create Flash Sale',
        href: FlashSaleController.create.url(),
    },
]);

const generateSlug = () => {
    if (form.name) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
};

const handleBannerSelect = (event) => {
    const file = event.files[0];
    if (file) {
        form.banner_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            bannerPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleBannerRemove = () => {
    form.banner_image = null;
    bannerPreview.value = null;
};

const addProduct = () => {
    flashSaleProducts.value.push({
        product_id: null,
        discount_type: null,
        discount_value: null,
        sort_order: 0,
    });
};

const removeProduct = (index) => {
    flashSaleProducts.value.splice(index, 1);
};

const save = () => {
    // Prepare products data
    const productsData = flashSaleProducts.value
        .filter(p => p.product_id)
        .map(p => ({
            product_id: p.product_id,
            discount_type: p.discount_type || null,
            discount_value: p.discount_value || null,
            sort_order: p.sort_order || 0,
        }));

    form.transform((data) => ({
        ...data,
        products: productsData,
    })).post(FlashSaleController.store.url(), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            bannerPreview.value = null;
            flashSaleProducts.value = [];
        }
    });
};

const cancel = () => {
    router.visit(FlashSaleController.index.url());
};
</script>

<template>
    <Head title="Create Flash Sale" />

    <AppSidebarLayout title="Create Flash Sale" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-sm font-medium">
                                    Flash Sale Name <span class="text-red-500">*</span>
                                </label>
                                <InputText 
                                    id="name" 
                                    v-model="form.name" 
                                    placeholder="Enter flash sale name..." 
                                    @blur="generateSlug"
                                    :class="{ 'p-invalid': form.errors.name }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="slug" class="text-sm font-medium">Slug</label>
                                <InputText 
                                    id="slug" 
                                    v-model="form.slug" 
                                    placeholder="flash-sale-slug" 
                                    :class="{ 'p-invalid': form.errors.slug }"
                                    class="w-full"
                                />
                                <small class="text-muted-foreground">Leave empty to auto-generate</small>
                                <InputError :message="form.errors.slug" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="text-sm font-medium">Description</label>
                            <Textarea 
                                id="description" 
                                v-model="form.description" 
                                placeholder="Enter flash sale description..." 
                                :class="{ 'p-invalid': form.errors.description }"
                                rows="3"
                                class="w-full"
                            />
                            <InputError :message="form.errors.description" />
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Date Range</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="start_date" class="text-sm font-medium">
                                    Start Date <span class="text-red-500">*</span>
                                </label>
                                <Calendar 
                                    id="start_date" 
                                    v-model="form.start_date" 
                                    showTime 
                                    hourFormat="12"
                                    :class="{ 'p-invalid': form.errors.start_date }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.start_date" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="end_date" class="text-sm font-medium">
                                    End Date <span class="text-red-500">*</span>
                                </label>
                                <Calendar 
                                    id="end_date" 
                                    v-model="form.end_date" 
                                    showTime 
                                    hourFormat="12"
                                    :class="{ 'p-invalid': form.errors.end_date }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.end_date" />
                            </div>
                        </div>
                    </div>

                    <!-- Banner Image -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Banner Image</h3>

                        <div class="flex flex-col gap-4">
                            <div v-if="bannerPreview" class="relative inline-block">
                                <img :src="bannerPreview" alt="Banner preview"
                                    class="h-48 w-full object-cover rounded-lg border" />
                                <Button type="button" icon="pi pi-times" severity="danger" size="small" rounded
                                    class="absolute -top-2 -right-2" @click="handleBannerRemove" />
                            </div>
                            <FileUpload mode="basic" accept="image/*" :maxFileSize="5000000" chooseLabel="Choose Banner Image"
                                :auto="false" @select="handleBannerSelect" />
                            <small class="text-muted-foreground">Max size: 5MB</small>
                            <InputError :message="form.errors.banner_image" />
                        </div>
                    </div>

                    <!-- Discount Settings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Discount Settings</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="discount_type" class="text-sm font-medium">
                                    Discount Type <span class="text-red-500">*</span>
                                </label>
                                <Dropdown 
                                    id="discount_type" 
                                    v-model="form.discount_type" 
                                    :options="typeOptions" 
                                    optionLabel="label" 
                                    optionValue="value" 
                                    :class="{ 'p-invalid': form.errors.discount_type }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.discount_type" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="discount_value" class="text-sm font-medium">Discount Value</label>
                                <InputNumber 
                                    id="discount_value" 
                                    v-model="form.discount_value" 
                                    :min="0"
                                    :max="maxValue"
                                    :suffix="form.discount_type === 'percentage' ? '%' : '$'"
                                    :class="{ 'p-invalid': form.errors.discount_value }"
                                    class="w-full"
                                />
                                <small class="text-muted-foreground">
                                    {{ form.discount_type === 'percentage' ? 'Percentage discount (0-100)' : 'Fixed amount discount' }}
                                </small>
                                <InputError :message="form.errors.discount_value" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="max_products" class="text-sm font-medium">Max Products</label>
                            <InputNumber 
                                id="max_products" 
                                v-model="form.max_products" 
                                :min="1"
                                :class="{ 'p-invalid': form.errors.max_products }"
                                class="w-full"
                            />
                            <small class="text-muted-foreground">Maximum number of products in this flash sale (optional)</small>
                            <InputError :message="form.errors.max_products" />
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h3 class="text-lg font-semibold">Products</h3>
                            <Button type="button" label="Add Product" icon="pi pi-plus" size="small" @click="addProduct" />
                        </div>

                        <div v-if="flashSaleProducts.length === 0" class="text-center py-4 text-gray-500">
                            No products added. Click "Add Product" to add one.
                        </div>

                        <div v-for="(product, index) in flashSaleProducts" :key="index" class="border rounded-lg p-4 space-y-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium">Product {{ index + 1 }}</span>
                                <Button type="button" icon="pi pi-times" severity="danger" size="small" text
                                    @click="removeProduct(index)" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Product <span class="text-red-500">*</span></label>
                                    <Dropdown 
                                        v-model="product.product_id" 
                                        :options="productOptions"
                                        optionLabel="name" 
                                        optionValue="id" 
                                        placeholder="Select product..." 
                                        class="w-full"
                                    />
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Discount Type</label>
                                    <Dropdown 
                                        v-model="product.discount_type" 
                                        :options="typeOptions"
                                        optionLabel="label" 
                                        optionValue="value" 
                                        placeholder="Use flash sale default" 
                                        showClear
                                        class="w-full"
                                    />
                                    <small class="text-muted-foreground">Leave empty to use flash sale default</small>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Discount Value</label>
                                    <InputNumber 
                                        v-model="product.discount_value" 
                                        :min="0"
                                        :max="product.discount_type === 'percentage' ? 100 : null"
                                        :suffix="product.discount_type === 'percentage' ? '%' : '$'"
                                        class="w-full"
                                    />
                                    <small class="text-muted-foreground">Leave empty to use flash sale default</small>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Sort Order</label>
                                    <InputNumber v-model="product.sort_order" :min="0" class="w-full" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Flags -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Status & Flags</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="sort_order" class="text-sm font-medium">Sort Order</label>
                            <InputNumber 
                                id="sort_order" 
                                v-model="form.sort_order" 
                                :min="0"
                                :class="{ 'p-invalid': form.errors.sort_order }"
                                class="w-full"
                            />
                            <InputError :message="form.errors.sort_order" />
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
                                <InputText 
                                    id="meta_title" 
                                    v-model="form.meta_title" 
                                    placeholder="SEO title..."
                                    :class="{ 'p-invalid': form.errors.meta_title }"
                                />
                                <small class="text-muted-foreground">
                                    {{ form.meta_title?.length || 0 }}/255 characters
                                </small>
                                <InputError :message="form.errors.meta_title" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="meta_description" class="text-sm font-medium">Meta Description</label>
                                <Textarea 
                                    id="meta_description" 
                                    v-model="form.meta_description"
                                    placeholder="SEO description..."
                                    rows="3"
                                    :class="{ 'p-invalid': form.errors.meta_description }"
                                />
                                <small class="text-muted-foreground">
                                    {{ form.meta_description?.length || 0 }}/500 characters
                                </small>
                                <InputError :message="form.errors.meta_description" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="meta_keywords" class="text-sm font-medium">Meta Keywords</label>
                                <InputText 
                                    id="meta_keywords" 
                                    v-model="form.meta_keywords"
                                    placeholder="keyword1, keyword2, keyword3..."
                                    :class="{ 'p-invalid': form.errors.meta_keywords }"
                                />
                                <InputError :message="form.errors.meta_keywords" />
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-2 mt-4 pt-4 border-t">
                        <Button type="button" label="Cancel" severity="secondary" outlined @click="cancel" />
                        <Button type="submit" label="Create Flash Sale" icon="pi pi-check" :loading="form.processing"
                            :disabled="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

