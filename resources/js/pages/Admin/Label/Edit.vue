<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { computed } from 'vue';
import LabelController from '@/actions/App/Http/Controllers/Admin/Label/LabelController';

const props = defineProps({
    label: {
        type: Object,
        required: true
    }
});

const form = useForm({
    name: props.label.name || '',
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Labels',
        href: LabelController.index.url(),
    },
    {
        title: `Edit: ${props.label.name}`,
        href: LabelController.edit.url(props.label.id),
    },
]);

function save() {
    form.put(LabelController.update.url(props.label.id), {
        preserveScroll: true,
    });
}

function cancel() {
    router.visit(LabelController.index.url());
}
</script>

<template>

    <Head :title="`Edit: ${label.name}`" />

    <AppSidebarLayout :title="`Edit: ${label.name}`" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="name" class="text-sm font-medium">
                            Label Name <span class="text-red-500">*</span>
                        </label>
                        <InputText id="name" v-model="form.name" placeholder="Enter label name..."
                            aria-describedby="name-help" class="w-full" />
                        <InputError :message="form.errors.name" />
                        <small id="name-help" class="text-muted-foreground">
                            Enter a unique label name
                        </small>
                    </div>

                    <div class="flex justify-end gap-2">
                        <Button label="Cancel" severity="secondary" outlined @click="cancel" />
                        <Button label="Update Label" type="submit" :loading="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
