<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import { computed } from 'vue';
import TagController from '@/actions/App/Http/Controllers/Admin/Tag/TagController';

const form = useForm({
    name: '',
    slug: '',
    description: '',
    color: '',
    is_active: true,
    sort_order: 0,
});

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
        title: 'Tags',
        href: TagController.index.url(),
    },
    {
        title: 'Create Tag',
        href: TagController.create.url(),
    },
]);

function save() {
    form.post(TagController.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
}

function cancel() {
    form.get(TagController.index.url());
}
</script>

<template>
    <Head title="Create Tag" />

    <AppSidebarLayout title="Create Tag" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="name" class="text-sm font-medium">
                            Tag Name <span class="text-red-500">*</span>
                        </label>
                        <InputText 
                            id="name" 
                            v-model="form.name" 
                            placeholder="Enter tag name..." 
                            :class="{ 'p-invalid': form.errors.name }"
                            aria-describedby="name-help"
                            class="w-full"
                            @blur="generateSlug"
                        />
                        <InputError :message="form.errors.name" />
                        <small id="name-help" class="text-muted-foreground">
                            Enter a unique tag name
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
                            placeholder="Enter tag description..." 
                            :class="{ 'p-invalid': form.errors.description }"
                            rows="3"
                            class="w-full"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="color" class="text-sm font-medium">Color</label>
                            <InputText 
                                id="color" 
                                v-model="form.color" 
                                placeholder="#FF0000"
                                maxlength="7"
                                :class="{ 'p-invalid': form.errors.color }"
                                class="w-full"
                            />
                            <InputError :message="form.errors.color" />
                            <small class="text-muted-foreground">
                                Hex color code (e.g., #FF0000)
                            </small>
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

                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <label class="text-sm font-medium">Active</label>
                            <p class="text-xs text-muted-foreground">Tag is active</p>
                        </div>
                        <InputSwitch v-model="form.is_active" />
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
                            label="Create Tag" 
                            type="submit"
                            :loading="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

