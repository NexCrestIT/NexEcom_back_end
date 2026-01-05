<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import MultiSelect from 'primevue/multiselect';
import { computed } from 'vue';
import { route } from '@/composables/useRouter';

const props = defineProps({
    roles: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: []
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Users',
        href: route('admin.users.index'),
    },
    {
        title: 'Create User',
        href: route('admin.users.create'),
    },
]);

// Transform roles for MultiSelect
const roleOptions = computed(() => {
    return props.roles.map(role => ({
        label: role.name,
        value: role.id
    }));
});

function save() {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
}
</script>

<template>
    <Head title="Create User" />

    <AppSidebarLayout title="Create User" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="name" class="text-sm font-medium">Name <span class="text-red-500">*</span></label>
                        <InputText 
                            id="name" 
                            v-model="form.name" 
                            placeholder="Enter user name..." 
                            :class="{ 'p-invalid': form.errors.name }"
                            aria-describedby="name-help" 
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="email" class="text-sm font-medium">Email <span class="text-red-500">*</span></label>
                        <InputText 
                            id="email" 
                            v-model="form.email" 
                            type="email"
                            placeholder="Enter user email..." 
                            :class="{ 'p-invalid': form.errors.email }"
                            aria-describedby="email-help" 
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password" class="text-sm font-medium">Password <span class="text-red-500">*</span></label>
                        <Password 
                            id="password" 
                            v-model="form.password" 
                            placeholder="Enter password..."
                            :class="{ 'p-invalid': form.errors.password }"
                            :feedback="false"
                            toggleMask
                            aria-describedby="password-help"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password_confirmation" class="text-sm font-medium">Confirm Password <span class="text-red-500">*</span></label>
                        <Password 
                            id="password_confirmation" 
                            v-model="form.password_confirmation" 
                            placeholder="Confirm password..."
                            :class="{ 'p-invalid': form.errors.password_confirmation }"
                            :feedback="false"
                            toggleMask
                            aria-describedby="password_confirmation-help"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="roles" class="text-sm font-medium">Roles</label>
                        <MultiSelect 
                            id="roles"
                            v-model="form.roles" 
                            :options="roleOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Select roles..."
                            :class="{ 'p-invalid': form.errors.roles }"
                            display="chip"
                            aria-describedby="roles-help"
                        />
                        <InputError :message="form.errors.roles" />
                        <small class="text-muted-foreground">Select one or more roles for this user</small>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">  
                        <Button 
                            type="button"
                            label="Cancel" 
                            severity="secondary"
                            outlined
                            @click="$inertia.visit(route('admin.users.index'))"
                        />
                        <Button 
                            type="submit"
                            label="Create User" 
                            :loading="form.processing"
                            :disabled="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

