<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { computed } from 'vue';
import ScentFamilyController from '@/actions/App/Http/Controllers/Admin/ScentFamily/ScentFamilyController';

const form = useForm({
    name: '',
    status: true,
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Scent Families',
        href: ScentFamilyController.index.url(),
    },
    {
        title: 'Create Scent Family',
        href: ScentFamilyController.create.url(),
    },
]);

function save() {
    form.post(ScentFamilyController.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
}

function cancel() {
    form.get(ScentFamilyController.index.url());
}
</script>

<template>
    <Head title="Create Scent Family" />

    <AppSidebarLayout title="Create Scent Family" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="name" class="text-sm font-medium">
                            Scent Family Name <span class="text-red-500">*</span>
                        </label>
                        <InputText id="name" v-model="form.name" placeholder="Enter scent family name..." aria-describedby="name-help" class="w-full" />
                        <InputError :message="form.errors.name" />
                        <small id="name-help" class="text-muted-foreground">
                            Enter a unique scent family name
                        </small>
                    </div>

                    <div class="flex justify-end gap-2">
                        <Button label="Cancel" severity="secondary" outlined @click="cancel" />
                        <Button label="Create Scent Family" type="submit" :loading="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

