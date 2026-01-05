<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import MultiSelect from 'primevue/multiselect';
import { computed, ref } from 'vue';
import DiscountController from '@/actions/App/Http/Controllers/Admin/Discount/DiscountController';

const form = useForm({
    name: '',
    code: '',
    description: '',
    type: 'percentage',
    value: 0,
    minimum_purchase: null,
    maximum_discount: null,
    usage_limit_per_user: null,
    total_usage_limit: null,
    start_date: null,
    end_date: null,
    is_active: true,
    is_first_time_only: false,
    free_shipping: false,
    applicable_categories: [],
    applicable_products: [],
    excluded_categories: [],
    excluded_products: [],
    sort_order: 0,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

const showSeoFields = ref(false);
const showAdvancedFields = ref(false);

const typeOptions = [
    { label: 'Percentage', value: 'percentage' },
    { label: 'Fixed Amount', value: 'fixed' },
];

const maxValue = computed(() => {
    return form.type === 'percentage' ? 100 : null;
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Discounts',
        href: DiscountController.index.url(),
    },
    {
        title: 'Create Discount',
        href: DiscountController.create.url(),
    },
]);

function save() {
    form.post(DiscountController.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
}

function cancel() {
    form.get(DiscountController.index.url());
}
</script>

<template>
    <Head title="Create Discount" />

    <AppSidebarLayout title="Create Discount" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-sm font-medium">
                                    Discount Name <span class="text-red-500">*</span>
                                </label>
                                <InputText 
                                    id="name" 
                                    v-model="form.name" 
                                    placeholder="Enter discount name..." 
                                    :class="{ 'p-invalid': form.errors.name }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="code" class="text-sm font-medium">Discount Code</label>
                                <InputText 
                                    id="code" 
                                    v-model="form.code" 
                                    placeholder="Auto-generated if left empty" 
                                    :class="{ 'p-invalid': form.errors.code }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.code" />
                                <small class="text-muted-foreground">
                                    Unique coupon code (auto-generated if left empty)
                                </small>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="text-sm font-medium">Description</label>
                            <Textarea 
                                id="description" 
                                v-model="form.description" 
                                placeholder="Enter discount description..." 
                                :class="{ 'p-invalid': form.errors.description }"
                                rows="3"
                                class="w-full"
                            />
                            <InputError :message="form.errors.description" />
                        </div>
                    </div>

                    <!-- Discount Details -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Discount Details</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="type" class="text-sm font-medium">
                                    Discount Type <span class="text-red-500">*</span>
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
                                <label for="value" class="text-sm font-medium">
                                    Discount Value <span class="text-red-500">*</span>
                                </label>
                                <InputNumber 
                                    id="value" 
                                    v-model="form.value" 
                                    :min="0"
                                    :max="maxValue"
                                    :suffix="form.type === 'percentage' ? '%' : '$'"
                                    :class="{ 'p-invalid': form.errors.value }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.value" />
                                <small class="text-muted-foreground">
                                    {{ form.type === 'percentage' ? 'Percentage (0-100)' : 'Fixed amount' }}
                                </small>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="minimum_purchase" class="text-sm font-medium">Minimum Purchase</label>
                                <InputNumber 
                                    id="minimum_purchase" 
                                    v-model="form.minimum_purchase" 
                                    :min="0"
                                    prefix="$"
                                    :class="{ 'p-invalid': form.errors.minimum_purchase }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.minimum_purchase" />
                                <small class="text-muted-foreground">
                                    Minimum order amount to apply discount
                                </small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="maximum_discount" class="text-sm font-medium">Maximum Discount</label>
                                <InputNumber 
                                    id="maximum_discount" 
                                    v-model="form.maximum_discount" 
                                    :min="0"
                                    prefix="$"
                                    :class="{ 'p-invalid': form.errors.maximum_discount }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.maximum_discount" />
                                <small class="text-muted-foreground">
                                    Maximum discount amount (for percentage discounts)
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Validity Period -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Validity Period</h3>

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
                                    :minDate="form.start_date"
                                    :class="{ 'p-invalid': form.errors.end_date }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.end_date" />
                            </div>
                        </div>
                    </div>

                    <!-- Usage Limits -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Usage Limits</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="usage_limit_per_user" class="text-sm font-medium">Usage Limit Per User</label>
                                <InputNumber 
                                    id="usage_limit_per_user" 
                                    v-model="form.usage_limit_per_user" 
                                    :min="1"
                                    :class="{ 'p-invalid': form.errors.usage_limit_per_user }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.usage_limit_per_user" />
                                <small class="text-muted-foreground">
                                    Maximum times a single user can use this discount
                                </small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="total_usage_limit" class="text-sm font-medium">Total Usage Limit</label>
                                <InputNumber 
                                    id="total_usage_limit" 
                                    v-model="form.total_usage_limit" 
                                    :min="1"
                                    :class="{ 'p-invalid': form.errors.total_usage_limit }"
                                    class="w-full"
                                />
                                <InputError :message="form.errors.total_usage_limit" />
                                <small class="text-muted-foreground">
                                    Maximum total times this discount can be used
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold border-b pb-2">Settings</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Active</label>
                                    <p class="text-xs text-muted-foreground">Discount is active</p>
                                </div>
                                <InputSwitch v-model="form.is_active" />
                            </div>

                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">First Time Only</label>
                                    <p class="text-xs text-muted-foreground">Only for first-time customers</p>
                                </div>
                                <InputSwitch v-model="form.is_first_time_only" />
                            </div>

                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <label class="text-sm font-medium">Free Shipping</label>
                                    <p class="text-xs text-muted-foreground">Apply free shipping</p>
                                </div>
                                <InputSwitch v-model="form.free_shipping" />
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

                    <!-- Advanced Options -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h3 class="text-lg font-semibold">Advanced Options</h3>
                            <Button 
                                :label="showAdvancedFields ? 'Hide Advanced' : 'Show Advanced'"
                                icon="pi pi-cog"
                                severity="secondary"
                                text
                                @click="showAdvancedFields = !showAdvancedFields"
                                type="button"
                            />
                        </div>
                        
                        <div v-if="showAdvancedFields" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Applicable Categories</label>
                                    <MultiSelect 
                                        v-model="form.applicable_categories" 
                                        :options="[]"
                                        placeholder="Select categories (optional)"
                                        class="w-full"
                                        display="chip"
                                    />
                                    <small class="text-muted-foreground">
                                        Leave empty to apply to all categories
                                    </small>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Excluded Categories</label>
                                    <MultiSelect 
                                        v-model="form.excluded_categories" 
                                        :options="[]"
                                        placeholder="Select categories to exclude"
                                        class="w-full"
                                        display="chip"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Applicable Products</label>
                                    <MultiSelect 
                                        v-model="form.applicable_products" 
                                        :options="[]"
                                        placeholder="Select products (optional)"
                                        class="w-full"
                                        display="chip"
                                    />
                                    <small class="text-muted-foreground">
                                        Leave empty to apply to all products
                                    </small>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Excluded Products</label>
                                    <MultiSelect 
                                        v-model="form.excluded_products" 
                                        :options="[]"
                                        placeholder="Select products to exclude"
                                        class="w-full"
                                        display="chip"
                                    />
                                </div>
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
                        <Button type="submit" label="Create Discount" icon="pi pi-check" :loading="form.processing"
                            :disabled="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

