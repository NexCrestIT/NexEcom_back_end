<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import Dropdown from 'primevue/dropdown';
import { computed, ref, watch, onMounted } from 'vue';
import OptionController from '@/actions/App/Http/Controllers/Admin/Option/OptionController';

const props = defineProps({
    option: {
        type: Object,
        required: true
    }
});

const form = useForm({
    name: props.option.name || '',
    slug: props.option.slug || '',
    description: props.option.description || '',
    type: props.option.type || 'text',
    value: '',
    is_active: props.option.is_active ?? true,
    is_required: props.option.is_required ?? false,
    sort_order: props.option.sort_order || 0,
    meta_title: props.option.meta_title || '',
    meta_description: props.option.meta_description || '',
    meta_keywords: props.option.meta_keywords || '',
});

const valueList = ref([]);
const showSeoFields = ref(false);

// Show SEO fields if any SEO data exists
if (props.option.meta_title || props.option.meta_description || props.option.meta_keywords) {
    showSeoFields.value = true;
}

const typeOptions = [
    { label: 'Text', value: 'text' },
    { label: 'Select', value: 'select' },
    { label: 'Multiselect', value: 'multiselect' },
    { label: 'Radio', value: 'radio' },
    { label: 'Checkbox', value: 'checkbox' },
];

const showValues = computed(() => {
    return ['select', 'multiselect', 'radio', 'checkbox'].includes(form.type);
});

const initializeValues = () => {
    if (props.option.value) {
        try {
            const parsed = typeof props.option.value === 'string' ? JSON.parse(props.option.value) : props.option.value;
            if (Array.isArray(parsed)) {
                valueList.value = parsed.map(item => ({
                    value: item.value || item,
                    label: item.label || item.value || item,
                }));
            } else {
                valueList.value = [];
                form.value = props.option.value;
            }
        } catch (e) {
            // If not JSON, treat as simple string
            valueList.value = [];
            form.value = props.option.value;
        }
    } else {
        valueList.value = [];
        form.value = '';
    }
};

onMounted(() => {
    initializeValues();
});

watch(() => props.option, () => {
    initializeValues();
}, { deep: true });

watch(() => form.type, (newType, oldType) => {
    if (showValues.value) {
        // If switching to a type that needs values, try to restore them
        if (oldType && !['select', 'multiselect', 'radio', 'checkbox'].includes(oldType)) {
            initializeValues();
        }
    } else {
        // If switching away from value-based types, clear values
        valueList.value = [];
        form.value = '';
    }
});

const generateSlug = () => {
    if (form.name && !form.slug) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
};

const addValue = () => {
    valueList.value.push({ value: '', label: '' });
};

const removeValue = (index) => {
    valueList.value.splice(index, 1);
    updateFormValues();
};

const updateFormValues = () => {
    if (showValues.value && valueList.value.length > 0) {
        form.value = JSON.stringify(valueList.value);
    } else {
        form.value = '';
    }
};

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Options',
        href: OptionController.index.url(),
    },
    {
        title: `Edit: ${props.option.name}`,
        href: OptionController.edit.url(props.option.id),
    },
]);

function save() {
    updateFormValues();
    form.post(OptionController.update.url(props.option.id), {
        preserveScroll: true,
    });
}

function cancel() {
    router.visit(OptionController.index.url());
}
</script>

<template>
    <Head :title="`Edit: ${option.name}`" />

    <AppSidebarLayout :title="`Edit: ${option.name}`" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-sm font-medium">
                                    Option Name <span class="text-red-500">*</span>
                                </label>
                                <InputText 
                                    id="name" 
                                    v-model="form.name" 
                                    placeholder="Enter option name..." 
                                    :class="{ 'p-invalid': form.errors.name }"
                                    aria-describedby="name-help"
                                    class="w-full"
                                    @blur="generateSlug"
                                />
                                <InputError :message="form.errors.name" />
                                <small id="name-help" class="text-muted-foreground">
                                    Enter a unique option name
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
                                placeholder="Enter option description..." 
                                :class="{ 'p-invalid': form.errors.description }"
                                rows="3"
                                class="w-full"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

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
                    </div>

                    <!-- Option Values -->
                    <div v-if="showValues" class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Option Values</h3>
                        
                        <div class="space-y-3">
                            <div v-for="(item, index) in valueList" :key="index" class="flex gap-2 items-end">
                                <div class="flex-1">
                                    <label class="text-sm font-medium">Value</label>
                                    <InputText 
                                        v-model="item.value" 
                                        placeholder="Enter value..."
                                        class="w-full"
                                        @input="updateFormValues"
                                    />
                                </div>
                                <div class="flex-1">
                                    <label class="text-sm font-medium">Label (Optional)</label>
                                    <InputText 
                                        v-model="item.label" 
                                        placeholder="Enter label..."
                                        class="w-full"
                                        @input="updateFormValues"
                                    />
                                </div>
                                <Button 
                                    type="button"
                                    icon="pi pi-trash" 
                                    severity="danger" 
                                    outlined
                                    @click="removeValue(index)"
                                />
                            </div>
                            <Button 
                                type="button"
                                label="Add Value" 
                                icon="pi pi-plus" 
                                severity="secondary"
                                outlined
                                @click="addValue"
                            />
                        </div>
                    </div>

                    <!-- Default Value (for text type) -->
                    <div v-if="!showValues" class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Default Value</h3>
                        <div class="flex flex-col gap-2">
                            <label for="value" class="text-sm font-medium">Value</label>
                            <InputText 
                                id="value" 
                                v-model="form.value" 
                                placeholder="Enter default value..." 
                                :class="{ 'p-invalid': form.errors.value }"
                                class="w-full"
                            />
                            <InputError :message="form.errors.value" />
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Settings</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Active</label>
                                    <p class="text-xs text-muted-foreground">Option is active</p>
                                </div>
                                <InputSwitch v-model="form.is_active" />
                            </div>

                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Required</label>
                                    <p class="text-xs text-muted-foreground">Option is required</p>
                                </div>
                                <InputSwitch v-model="form.is_required" />
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
                        <Button type="submit" label="Update Option" icon="pi pi-check" :loading="form.processing"
                            :disabled="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

