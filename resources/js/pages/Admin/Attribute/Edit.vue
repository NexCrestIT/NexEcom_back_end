<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import InputSwitch from 'primevue/inputswitch';
import Tag from 'primevue/tag';
import { computed, ref, watch, onMounted } from 'vue';
import AttributeController from '@/actions/App/Http/Controllers/Admin/Attribute/AttributeController';

const props = defineProps({
    attribute: {
        type: Object,
        required: true
    }
});

const form = useForm({
    name: props.attribute.name || '',
    slug: props.attribute.slug || '',
    description: props.attribute.description || '',
    type: props.attribute.type || 'text',
    is_required: props.attribute.is_required || false,
    is_filterable: props.attribute.is_filterable ?? true,
    is_searchable: props.attribute.is_searchable ?? true,
    is_active: props.attribute.is_active ?? true,
    default_value: props.attribute.default_value || '',
    validation_rules: props.attribute.validation_rules || null,
    values: [],
    sort_order: props.attribute.sort_order || 0,
});

const showValues = computed(() => {
    return ['select', 'multiselect'].includes(form.type);
});

const typeOptions = [
    { label: 'Text', value: 'text' },
    { label: 'Number', value: 'number' },
    { label: 'Select', value: 'select' },
    { label: 'Multiselect', value: 'multiselect' },
    { label: 'Boolean', value: 'boolean' },
    { label: 'Date', value: 'date' },
    { label: 'Textarea', value: 'textarea' },
];

// Initialize values from attribute
const initializeValues = () => {
    if (props.attribute.values && Array.isArray(props.attribute.values)) {
        valuesList.value = props.attribute.values.map(val => ({
            id: val.id,
            value: val.value || '',
            display_value: val.display_value || '',
            color_code: val.color_code || '',
            is_active: val.is_active ?? true,
            sort_order: val.sort_order || 0,
        }));
        updateFormValues();
    } else {
        valuesList.value = [];
        form.values = [];
    }
};

const valuesList = ref([]);

// Initialize values when component mounts or attribute changes
onMounted(() => {
    initializeValues();
});

// Watch for attribute prop changes (in case Inertia updates data)
watch(() => props.attribute.values, () => {
    initializeValues();
}, { deep: true });

// Watch for type changes
watch(() => form.type, (newType) => {
    if (!['select', 'multiselect'].includes(newType)) {
        valuesList.value = [];
        form.values = [];
    } else {
        // Re-initialize values if type changes back to select/multiselect
        initializeValues();
    }
});

const addValue = () => {
    valuesList.value.push({
        value: '',
        display_value: '',
        color_code: '',
        is_active: true,
        sort_order: valuesList.value.length,
    });
    updateFormValues();
};

const removeValue = (index) => {
    valuesList.value.splice(index, 1);
    updateFormValues();
};

const updateFormValues = () => {
    form.values = valuesList.value.map((val, idx) => ({
        ...val,
        sort_order: idx,
    }));
};


const generateSlug = () => {
    if (form.name && !form.slug) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
};

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Attributes',
        href: AttributeController.index.url(),
    },
    {
        title: `Edit: ${props.attribute.name}`,
        href: AttributeController.edit.url(props.attribute.id),
    },
]);

function save() {
    form.put(AttributeController.update.url(props.attribute.id), {
        preserveScroll: true,
    });
}

function cancel() {
    router.visit(AttributeController.index.url());
}
</script>

<template>
    <Head :title="`Edit: ${attribute.name}`" />

    <AppSidebarLayout :title="`Edit: ${attribute.name}`" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Basic Information</h3>
                        
                        <div class="flex flex-col gap-2">
                            <label for="name" class="text-sm font-medium">
                                Attribute Name <span class="text-red-500">*</span>
                            </label>
                            <InputText 
                                id="name" 
                                v-model="form.name" 
                                placeholder="Enter attribute name..." 
                                :class="{ 'p-invalid': form.errors.name }"
                                aria-describedby="name-help"
                                class="w-full"
                                @blur="generateSlug"
                            />
                            <InputError :message="form.errors.name" />
                            <small id="name-help" class="text-muted-foreground">
                                Enter a unique attribute name
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

                        <div class="flex flex-col gap-2">
                            <label for="description" class="text-sm font-medium">Description</label>
                            <Textarea 
                                id="description" 
                                v-model="form.description" 
                                placeholder="Enter attribute description..." 
                                :class="{ 'p-invalid': form.errors.description }"
                                rows="3"
                                class="w-full"
                            />
                            <InputError :message="form.errors.description" />
                        </div>
                    </div>

                    <!-- Attribute Configuration -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Configuration</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="type" class="text-sm font-medium">
                                    Type <span class="text-red-500">*</span>
                                </label>
                                <Dropdown 
                                    id="type"
                                    v-model="form.type" 
                                    :options="typeOptions" 
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select Type"
                                    :class="{ 'p-invalid': form.errors.type }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.type" />
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

                        <!-- Attribute Values for Select/Multiselect -->
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium">
                                    Attribute Values 
                                    <span v-if="showValues" class="text-red-500">*</span>
                                </label>
                                <Button 
                                    v-if="showValues"
                                    label="Add Value" 
                                    icon="pi pi-plus"
                                    size="small"
                                    @click="addValue"
                                    type="button"
                                />
                            </div>
                            
                            <!-- Show message when type is not select/multiselect -->
                            <div v-if="!showValues" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <i class="pi pi-info-circle mr-2"></i>
                                    Select "Select" or "Multiselect" as the attribute type to add values.
                                </p>
                            </div>
                            
                            <!-- Show values form when type is select/multiselect -->
                            <div v-if="showValues">
                                <div v-if="valuesList.length > 0" class="space-y-3">
                                    <div 
                                        v-for="(valueItem, index) in valuesList" 
                                        :key="valueItem.id || index"
                                        class="p-4 border rounded-lg space-y-3"
                                    >
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div class="flex flex-col gap-1">
                                                    <label class="text-xs font-medium text-gray-600">Value <span class="text-red-500">*</span></label>
                                                    <InputText 
                                                        v-model="valueItem.value" 
                                                        placeholder="e.g., Red, Small, etc."
                                                        class="w-full"
                                                        @input="updateFormValues"
                                                    />
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <label class="text-xs font-medium text-gray-600">Display Value</label>
                                                    <InputText 
                                                        v-model="valueItem.display_value" 
                                                        placeholder="Optional display text"
                                                        class="w-full"
                                                        @input="updateFormValues"
                                                    />
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <label class="text-xs font-medium text-gray-600">Color Code</label>
                                                    <InputText 
                                                        v-model="valueItem.color_code" 
                                                        placeholder="#FF0000"
                                                        class="w-full"
                                                        maxlength="7"
                                                        @input="updateFormValues"
                                                    />
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <InputSwitch 
                                                        v-model="valueItem.is_active" 
                                                        @update:modelValue="updateFormValues"
                                                    />
                                                    <label class="text-xs text-gray-600">Active</label>
                                                </div>
                                            </div>
                                            <Button 
                                                icon="pi pi-trash"
                                                severity="danger"
                                                text
                                                rounded
                                                @click="removeValue(index)"
                                                type="button"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded-lg">
                                    No values added. Click "Add Value" button above to create attribute values.
                                </div>
                                <InputError :message="form.errors.values" />
                                <small class="text-muted-foreground">
                                    Add values for select/multiselect type attributes. Each value can have a display name and color code.
                                </small>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="default_value" class="text-sm font-medium">Default Value</label>
                            <InputText 
                                id="default_value" 
                                v-model="form.default_value" 
                                placeholder="Enter default value (optional)" 
                                :class="{ 'p-invalid': form.errors.default_value }"
                                class="w-full"
                            />
                            <InputError :message="form.errors.default_value" />
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Settings</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Required</label>
                                    <p class="text-xs text-muted-foreground">Attribute is required</p>
                                </div>
                                <InputSwitch v-model="form.is_required" />
                            </div>

                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Filterable</label>
                                    <p class="text-xs text-muted-foreground">Can be used in filters</p>
                                </div>
                                <InputSwitch v-model="form.is_filterable" />
                            </div>

                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Searchable</label>
                                    <p class="text-xs text-muted-foreground">Can be searched</p>
                                </div>
                                <InputSwitch v-model="form.is_searchable" />
                            </div>

                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Active</label>
                                    <p class="text-xs text-muted-foreground">Attribute is active</p>
                                </div>
                                <InputSwitch v-model="form.is_active" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <Button 
                            label="Cancel" 
                            severity="secondary" 
                            outlined
                            @click="cancel"
                            type="button"
                        />
                        <Button 
                            label="Update Attribute" 
                            type="submit"
                            :loading="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

