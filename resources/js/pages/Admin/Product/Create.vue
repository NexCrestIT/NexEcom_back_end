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
import MultiSelect from 'primevue/multiselect';
import { computed, ref, watch } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/Admin/Product/ProductController';

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    },
    brands: {
        type: Array,
        default: () => []
    },
    collections: {
        type: Array,
        default: () => []
    },
    genders: {
        type: Array,
        default: () => []
    },
    tags: {
        type: Array,
        default: () => []
    },
    labels: {
        type: Array,
        default: () => []
    },
    discounts: {
        type: Array,
        default: () => []
    },
    attributes: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    name: '',
    slug: '',
    sku: '',
    short_description: '',
    description: '',
    specifications: '',
    price: 0,
    compare_at_price: null,
    cost_price: null,
    stock_quantity: 0,
    track_inventory: true,
    low_stock_threshold: null,
    allow_backorder: false,
    main_image: null,
    gallery_images: [],
    category_id: null,
    brand_id: null,
    collection_id: null,
    gender_id: null,
    tags: [],
    labels: [],
    discounts: [],
    attributes: [],
    is_active: true,
    is_featured: false,
    is_new: false,
    is_bestseller: false,
    is_digital: false,
    is_virtual: false,
    weight: null,
    weight_unit: 'kg',
    length: null,
    width: null,
    height: null,
    dimension_unit: 'cm',
    taxable: true,
    tax_rate: null,
    requires_shipping: true,
    shipping_weight: null,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    sort_order: 0,
});

const mainImagePreview = ref(null);
const galleryPreviews = ref([]);
const showSeoFields = ref(false);
const showAdvancedFields = ref(false);

const weightUnitOptions = [
    { label: 'kg', value: 'kg' },
    { label: 'g', value: 'g' },
    { label: 'lb', value: 'lb' },
    { label: 'oz', value: 'oz' },
];

const dimensionUnitOptions = [
    { label: 'cm', value: 'cm' },
    { label: 'm', value: 'm' },
    { label: 'in', value: 'in' },
    { label: 'ft', value: 'ft' },
];

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Products',
        href: ProductController.index.url(),
    },
    {
        title: 'Create Product',
        href: ProductController.create.url(),
    },
]);

// Transform options for dropdowns
const categoryOptions = computed(() => {
    return [{ id: null, name: '— Select Category —' }, ...props.categories];
});

const brandOptions = computed(() => {
    return [{ id: null, name: '— Select Brand —' }, ...props.brands];
});

const collectionOptions = computed(() => {
    return [{ id: null, name: '— Select Collection —' }, ...props.collections];
});

const genderOptions = computed(() => {
    return [{ id: null, name: '— Select Gender —' }, ...props.genders];
});

const tagOptions = computed(() => {
    return props.tags.map(tag => ({ id: tag.id, name: tag.name }));
});

const labelOptions = computed(() => {
    return props.labels.map(label => ({ id: label.id, name: label.name }));
});

const discountOptions = computed(() => {
    return props.discounts.map(discount => ({ id: discount.id, name: discount.name }));
});

// Product attributes management
const productAttributes = ref([]);

const addAttribute = () => {
    productAttributes.value.push({
        attribute_id: null,
        attribute_value_id: null,
        value: '',
        sort_order: 0,
    });
};

const removeAttribute = (index) => {
    productAttributes.value.splice(index, 1);
};

const getAttributeValues = (attributeId) => {
    const attribute = props.attributes.find(attr => attr.id === attributeId);
    if (!attribute || !attribute.values) return [];
    // Format values to have a display label
    return attribute.values.map(val => ({
        id: val.id,
        value: val.value,
        display_value: val.display_value || val.value,
        label: val.display_value || val.value, // Add label for dropdown
    }));
};

const handleMainImageSelect = (event) => {
    const file = event.files[0];
    if (file) {
        form.main_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            mainImagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleMainImageRemove = () => {
    form.main_image = null;
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
    form.gallery_images.splice(index, 1);
    galleryPreviews.value.splice(index, 1);
};

const generateSlug = () => {
    if (form.name) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
};

const generateSku = () => {
    if (!form.sku && form.name) {
        const prefix = form.name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '');
        const random = Math.random().toString(36).substring(2, 10).toUpperCase();
        form.sku = `${prefix}-${random}`;
    }
};

watch(() => form.name, () => {
    if (!form.slug) {
        generateSlug();
    }
    if (!form.sku) {
        generateSku();
    }
});

const save = () => {
    // Prepare attributes data
    const attributesData = productAttributes.value
        .filter(attr => attr.attribute_id)
        .map(attr => ({
            attribute_id: attr.attribute_id,
            attribute_value_id: attr.attribute_value_id || null,
            value: attr.value || null,
            sort_order: attr.sort_order || 0,
        }));

    form.transform((data) => ({
        ...data,
        attributes: attributesData,
    })).post(ProductController.store.url(), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            mainImagePreview.value = null;
            galleryPreviews.value = [];
            productAttributes.value = [];
        }
    });
};

const cancel = () => {
    router.visit(ProductController.index.url());
};
</script>

<template>
    <Head title="Create Product" />

    <AppSidebarLayout title="Create Product" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-sm font-medium">
                                    Product Name <span class="text-red-500">*</span>
                                </label>
                                <InputText 
                                    id="name" 
                                    v-model="form.name" 
                                    placeholder="Enter product name..."
                                    @blur="generateSlug"
                                    :class="{ 'p-invalid': form.errors.name }"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="slug" class="text-sm font-medium">Slug</label>
                                <InputText 
                                    id="slug" 
                                    v-model="form.slug" 
                                    placeholder="product-slug"
                                    :class="{ 'p-invalid': form.errors.slug }"
                                />
                                <small class="text-muted-foreground">Leave empty to auto-generate</small>
                                <InputError :message="form.errors.slug" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="sku" class="text-sm font-medium">SKU</label>
                                <InputText 
                                    id="sku" 
                                    v-model="form.sku" 
                                    placeholder="Product SKU"
                                    :class="{ 'p-invalid': form.errors.sku }"
                                />
                                <small class="text-muted-foreground">Leave empty to auto-generate</small>
                                <InputError :message="form.errors.sku" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="category_id" class="text-sm font-medium">Category</label>
                                <Dropdown 
                                    id="category_id" 
                                    v-model="form.category_id" 
                                    :options="categoryOptions"
                                    optionLabel="name" 
                                    optionValue="id" 
                                    placeholder="Select category..." 
                                    showClear
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.category_id }"
                                />
                                <InputError :message="form.errors.category_id" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="short_description" class="text-sm font-medium">Short Description</label>
                            <Textarea 
                                id="short_description" 
                                v-model="form.short_description"
                                placeholder="Brief product description..."
                                rows="2"
                                :class="{ 'p-invalid': form.errors.short_description }"
                            />
                            <InputError :message="form.errors.short_description" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="text-sm font-medium">Description</label>
                            <Textarea 
                                id="description" 
                                v-model="form.description"
                                placeholder="Full product description..."
                                rows="5"
                                :class="{ 'p-invalid': form.errors.description }"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="specifications" class="text-sm font-medium">Specifications</label>
                            <Textarea 
                                id="specifications" 
                                v-model="form.specifications"
                                placeholder="Product specifications..."
                                rows="4"
                                :class="{ 'p-invalid': form.errors.specifications }"
                            />
                            <InputError :message="form.errors.specifications" />
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Pricing</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="price" class="text-sm font-medium">
                                    Price <span class="text-red-500">*</span>
                                </label>
                                <InputNumber 
                                    id="price" 
                                    v-model="form.price" 
                                    :min="0"
                                    prefix="$"
                                    :class="{ 'p-invalid': form.errors.price }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.price" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="compare_at_price" class="text-sm font-medium">Compare At Price</label>
                                <InputNumber 
                                    id="compare_at_price" 
                                    v-model="form.compare_at_price" 
                                    :min="0"
                                    prefix="$"
                                    :class="{ 'p-invalid': form.errors.compare_at_price }"
                                    class="w-full"
                                />
                                <small class="text-muted-foreground">Original price (for showing discount)</small>
                                <InputError :message="form.errors.compare_at_price" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="cost_price" class="text-sm font-medium">Cost Price</label>
                                <InputNumber 
                                    id="cost_price" 
                                    v-model="form.cost_price" 
                                    :min="0"
                                    prefix="$"
                                    :class="{ 'p-invalid': form.errors.cost_price }"
                                    class="w-full"
                                />
                                <small class="text-muted-foreground">Internal cost (not shown to customers)</small>
                                <InputError :message="form.errors.cost_price" />
                            </div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Inventory</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="stock_quantity" class="text-sm font-medium">Stock Quantity</label>
                                <InputNumber 
                                    id="stock_quantity" 
                                    v-model="form.stock_quantity" 
                                    :min="0"
                                    :class="{ 'p-invalid': form.errors.stock_quantity }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.stock_quantity" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="low_stock_threshold" class="text-sm font-medium">Low Stock Threshold</label>
                                <InputNumber 
                                    id="low_stock_threshold" 
                                    v-model="form.low_stock_threshold" 
                                    :min="0"
                                    :class="{ 'p-invalid': form.errors.low_stock_threshold }"
                                    class="w-full"
                                />
                                <small class="text-muted-foreground">Alert when stock falls below this number</small>
                                <InputError :message="form.errors.low_stock_threshold" />
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.track_inventory" inputId="track_inventory" />
                                <label for="track_inventory" class="text-sm font-medium cursor-pointer">
                                    Track Inventory
                                </label>
                            </div>

                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.allow_backorder" inputId="allow_backorder" />
                                <label for="allow_backorder" class="text-sm font-medium cursor-pointer">
                                    Allow Backorder
                                </label>
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
                                    <img :src="mainImagePreview" alt="Preview"
                                        class="h-32 w-32 object-cover rounded-lg border" />
                                    <Button type="button" icon="pi pi-times" severity="danger" size="small" rounded
                                        class="absolute -top-2 -right-2" @click="handleMainImageRemove" />
                                </div>
                                <FileUpload mode="basic" accept="image/*" :maxFileSize="5000000" chooseLabel="Choose Main Image"
                                    :auto="false" @select="handleMainImageSelect" />
                                <small class="text-muted-foreground">Max size: 5MB</small>
                                <InputError :message="form.errors.main_image" />
                            </div>

                            <div>
                                <label class="text-sm font-medium mb-2 block">Gallery Images</label>
                                <div v-if="galleryPreviews.length > 0" class="flex flex-wrap gap-2 mb-2">
                                    <div v-for="(preview, index) in galleryPreviews" :key="index" class="relative">
                                        <img :src="preview" alt="Gallery preview"
                                            class="h-24 w-24 object-cover rounded-lg border" />
                                        <Button type="button" icon="pi pi-times" severity="danger" size="small" rounded
                                            class="absolute -top-2 -right-2" @click="removeGalleryImage(index)" />
                                    </div>
                                </div>
                                <FileUpload mode="basic" accept="image/*" :maxFileSize="5000000" chooseLabel="Add Gallery Image"
                                    :auto="false" @select="handleGallerySelect" :multiple="true" />
                                <small class="text-muted-foreground">Max size: 5MB per image</small>
                                <InputError :message="form.errors.gallery_images" />
                            </div>
                        </div>
                    </div>

                    <!-- Relationships -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Relationships</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="brand_id" class="text-sm font-medium">Brand</label>
                                <Dropdown 
                                    id="brand_id" 
                                    v-model="form.brand_id" 
                                    :options="brandOptions"
                                    optionLabel="name" 
                                    optionValue="id" 
                                    placeholder="Select brand..." 
                                    showClear
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.brand_id }"
                                />
                                <InputError :message="form.errors.brand_id" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="collection_id" class="text-sm font-medium">Collection</label>
                                <Dropdown 
                                    id="collection_id" 
                                    v-model="form.collection_id" 
                                    :options="collectionOptions"
                                    optionLabel="name" 
                                    optionValue="id" 
                                    placeholder="Select collection..." 
                                    showClear
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.collection_id }"
                                />
                                <InputError :message="form.errors.collection_id" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="gender_id" class="text-sm font-medium">Gender</label>
                                <Dropdown 
                                    id="gender_id" 
                                    v-model="form.gender_id" 
                                    :options="genderOptions"
                                    optionLabel="name" 
                                    optionValue="id" 
                                    placeholder="Select gender..." 
                                    showClear
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.gender_id }"
                                />
                                <InputError :message="form.errors.gender_id" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="tags" class="text-sm font-medium">Tags</label>
                                <MultiSelect 
                                    id="tags" 
                                    v-model="form.tags" 
                                    :options="tagOptions"
                                    optionLabel="name" 
                                    optionValue="id" 
                                    placeholder="Select tags..." 
                                    display="chip"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.tags }"
                                />
                                <InputError :message="form.errors.tags" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="labels" class="text-sm font-medium">Labels</label>
                                <MultiSelect 
                                    id="labels" 
                                    v-model="form.labels" 
                                    :options="labelOptions"
                                    optionLabel="name" 
                                    optionValue="id" 
                                    placeholder="Select labels..." 
                                    display="chip"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.labels }"
                                />
                                <InputError :message="form.errors.labels" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="discounts" class="text-sm font-medium">Discounts</label>
                            <MultiSelect 
                                id="discounts" 
                                v-model="form.discounts" 
                                :options="discountOptions"
                                optionLabel="name" 
                                optionValue="id" 
                                placeholder="Select discounts..." 
                                display="chip"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.discounts }"
                            />
                            <InputError :message="form.errors.discounts" />
                        </div>
                    </div>

                    <!-- Product Attributes -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h3 class="text-lg font-semibold">Product Attributes</h3>
                            <Button type="button" label="Add Attribute" icon="pi pi-plus" size="small" @click="addAttribute" />
                        </div>

                        <div v-if="productAttributes.length === 0" class="text-center py-4 text-gray-500">
                            No attributes added. Click "Add Attribute" to add one.
                        </div>

                        <div v-for="(attr, index) in productAttributes" :key="index" class="border rounded-lg p-4 space-y-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium">Attribute {{ index + 1 }}</span>
                                <Button type="button" icon="pi pi-times" severity="danger" size="small" text
                                    @click="removeAttribute(index)" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Attribute</label>
                                    <Dropdown 
                                        v-model="attr.attribute_id" 
                                        :options="attributes"
                                        optionLabel="name" 
                                        optionValue="id" 
                                        placeholder="Select attribute..." 
                                        class="w-full"
                                    />
                                </div>

                                <div v-if="attr.attribute_id" class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Value</label>
                                    <template v-if="getAttributeValues(attr.attribute_id).length > 0">
                                        <Dropdown 
                                            v-model="attr.attribute_value_id" 
                                            :options="getAttributeValues(attr.attribute_id)"
                                            optionLabel="label" 
                                            optionValue="id" 
                                            placeholder="Select value..." 
                                            showClear
                                            class="w-full"
                                        />
                                    </template>
                                    <InputText 
                                        v-else
                                        v-model="attr.value" 
                                        placeholder="Enter value..."
                                        class="w-full"
                                    />
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium">Sort Order</label>
                                <InputNumber v-model="attr.sort_order" :min="0" class="w-full" />
                            </div>
                        </div>
                    </div>

                    <!-- Physical Attributes -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Physical Attributes</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="weight" class="text-sm font-medium">Weight</label>
                                <div class="flex gap-2">
                                    <InputNumber 
                                        id="weight" 
                                        v-model="form.weight" 
                                        :min="0"
                                        class="flex-1"
                                        :class="{ 'p-invalid': form.errors.weight }"
                                    />
                                    <Dropdown 
                                        v-model="form.weight_unit" 
                                        :options="weightUnitOptions"
                                        optionLabel="label" 
                                        optionValue="value" 
                                        class="w-24"
                                    />
                                </div>
                                <InputError :message="form.errors.weight" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="shipping_weight" class="text-sm font-medium">Shipping Weight</label>
                                <InputNumber 
                                    id="shipping_weight" 
                                    v-model="form.shipping_weight" 
                                    :min="0"
                                    :class="{ 'p-invalid': form.errors.shipping_weight }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.shipping_weight" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="length" class="text-sm font-medium">Length</label>
                                <InputNumber 
                                    id="length" 
                                    v-model="form.length" 
                                    :min="0"
                                    :class="{ 'p-invalid': form.errors.length }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.length" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="width" class="text-sm font-medium">Width</label>
                                <InputNumber 
                                    id="width" 
                                    v-model="form.width" 
                                    :min="0"
                                    :class="{ 'p-invalid': form.errors.width }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.width" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="height" class="text-sm font-medium">Height</label>
                                <InputNumber 
                                    id="height" 
                                    v-model="form.height" 
                                    :min="0"
                                    :class="{ 'p-invalid': form.errors.height }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.height" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="dimension_unit" class="text-sm font-medium">Unit</label>
                                <Dropdown 
                                    id="dimension_unit" 
                                    v-model="form.dimension_unit" 
                                    :options="dimensionUnitOptions"
                                    optionLabel="label" 
                                    optionValue="value" 
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Tax & Shipping -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Tax & Shipping</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.taxable" inputId="taxable" />
                                <label for="taxable" class="text-sm font-medium cursor-pointer">
                                    Taxable
                                </label>
                            </div>

                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.requires_shipping" inputId="requires_shipping" />
                                <label for="requires_shipping" class="text-sm font-medium cursor-pointer">
                                    Requires Shipping
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="tax_rate" class="text-sm font-medium">Tax Rate (%)</label>
                            <InputNumber 
                                id="tax_rate" 
                                v-model="form.tax_rate" 
                                :min="0"
                                :max="100"
                                suffix="%"
                                :class="{ 'p-invalid': form.errors.tax_rate }"
                                class="w-full"
                            />
                            <InputError :message="form.errors.tax_rate" />
                        </div>
                    </div>

                    <!-- Status Flags -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Status & Flags</h3>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
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

                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.is_new" inputId="is_new" />
                                <label for="is_new" class="text-sm font-medium cursor-pointer">
                                    New
                                </label>
                            </div>

                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.is_bestseller" inputId="is_bestseller" />
                                <label for="is_bestseller" class="text-sm font-medium cursor-pointer">
                                    Bestseller
                                </label>
                            </div>

                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.is_digital" inputId="is_digital" />
                                <label for="is_digital" class="text-sm font-medium cursor-pointer">
                                    Digital
                                </label>
                            </div>

                            <div class="flex items-center gap-3">
                                <InputSwitch v-model="form.is_virtual" inputId="is_virtual" />
                                <label for="is_virtual" class="text-sm font-medium cursor-pointer">
                                    Virtual
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
                        <Button type="submit" label="Create Product" icon="pi pi-check" :loading="form.processing"
                            :disabled="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

