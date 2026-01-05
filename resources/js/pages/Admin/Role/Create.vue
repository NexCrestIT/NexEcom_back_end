<script setup>
import InputError from '@/components/InputError.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';
import { computed, ref } from 'vue';
import { route } from '@/composables/useRouter';

const props = defineProps({
    permissions: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    name: '',
    guard_name: 'web',
    permissions: []
});

// Track expanded accordion panels
const expandedPanels = ref([]);

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Roles',
        href: route('admin.roles.index'),
    },
    {
        title: 'Create Role',
        href: route('admin.roles.create'),
    },
]);

// Transform permissions grouped by module
const permissionsByModule = computed(() => {
    const grouped = {};
    Object.keys(props.permissions).forEach(module => {
        grouped[module] = props.permissions[module].map(permission => ({
            id: permission.id,
            name: permission.name,
            module: permission.module
        }));
    });
    return grouped;
});

const togglePermission = (permissionId) => {
    const index = form.permissions.indexOf(permissionId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permissionId);
    }
};

const isPermissionSelected = (permissionId) => {
    return form.permissions.includes(permissionId);
};

const selectAllInModule = (module) => {
    const modulePermissions = permissionsByModule.value[module] || [];
    const allSelected = modulePermissions.every(p => isPermissionSelected(p.id));
    
    if (allSelected) {
        // Deselect all in module
        modulePermissions.forEach(p => {
            const index = form.permissions.indexOf(p.id);
            if (index > -1) {
                form.permissions.splice(index, 1);
            }
        });
    } else {
        // Select all in module
        modulePermissions.forEach(p => {
            if (!isPermissionSelected(p.id)) {
                form.permissions.push(p.id);
            }
        });
    }
};

const isModuleFullySelected = (module) => {
    const modulePermissions = permissionsByModule.value[module] || [];
    if (modulePermissions.length === 0) return false;
    return modulePermissions.every(p => isPermissionSelected(p.id));
};

// Get total permissions count
const totalPermissionsCount = computed(() => {
    return Object.values(permissionsByModule.value).reduce((total, perms) => total + perms.length, 0);
});

// Check if all permissions are selected
const isAllSelected = computed(() => {
    if (totalPermissionsCount.value === 0) return false;
    return form.permissions.length === totalPermissionsCount.value;
});

// Toggle all permissions globally
const toggleAllPermissions = () => {
    if (isAllSelected.value) {
        // Deselect all
        form.permissions = [];
    } else {
        // Select all
        const allPermissionIds = [];
        Object.values(permissionsByModule.value).forEach(perms => {
            perms.forEach(p => allPermissionIds.push(p.id));
        });
        form.permissions = allPermissionIds;
    }
};

function save() {
    form.post(route('admin.roles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
}
</script>

<template>
    <Head title="Create Role" />

    <AppSidebarLayout title="Create Role" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-4 p-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <form @submit.prevent="save" class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="name" class="text-sm font-medium">Role Name <span class="text-red-500">*</span></label>
                        <InputText 
                            id="name" 
                            v-model="form.name" 
                            placeholder="Enter role name (e.g., Admin, Manager)..." 
                            :class="{ 'p-invalid': form.errors.name }"
                            aria-describedby="name-help" 
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium">Permissions</label>
                            <div class="flex items-center gap-4">
                                <label 
                                    class="flex items-center gap-2 cursor-pointer select-none"
                                >
                                    <Checkbox 
                                        :modelValue="isAllSelected"
                                        :binary="true"
                                        @update:modelValue="toggleAllPermissions"
                                    />
                                    <span class="text-sm font-medium">
                                        Select All
                                    </span>
                                </label>
                                <small class="text-muted-foreground">Selected: {{ form.permissions.length }}/{{ totalPermissionsCount }}</small>
                            </div>
                        </div>
                        
                        <div v-if="Object.keys(permissionsByModule).length === 0" class="text-center py-4 text-muted-foreground">
                            No permissions available
                        </div>

                        <div v-else class="flex flex-col gap-4">
                            <Accordion 
                                v-for="(permissions, module) in permissionsByModule" 
                                :key="module"
                                :value="[module]"
                                multiple 
                                class="rounded-xl border border-border bg-card shadow-sm overflow-hidden"
                            >
                                <AccordionPanel :value="module">
                                    <AccordionHeader class="bg-muted/40 hover:bg-muted/60 transition-colors">
                                        <div class="flex items-center justify-between w-full pr-4">
                                            <div class="flex items-center gap-3">
                                                <span class="font-semibold">{{ module }}</span>
                                                <span class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary">
                                                    {{ permissions.filter(p => isPermissionSelected(p.id)).length }}/{{ permissions.length }}
                                                </span>
                                            </div>
                                            <div 
                                                class="flex items-center gap-2 cursor-pointer" 
                                                @click.stop="selectAllInModule(module)"
                                            >
                                                <Checkbox 
                                                    :inputId="`select-all-${module}`"
                                                    :modelValue="isModuleFullySelected(module)"
                                                    :binary="true"
                                                />
                                                <label class="text-sm cursor-pointer">
                                                    Select All
                                                </label>
                                            </div>
                                        </div>
                                    </AccordionHeader>
                                    <AccordionContent>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 p-4">
                                            <div 
                                                v-for="permission in permissions" 
                                                :key="permission.id"
                                                class="flex items-center gap-2 p-2 rounded hover:bg-muted cursor-pointer"
                                                @click="togglePermission(permission.id)"
                                            >
                                                <Checkbox 
                                                    :inputId="`permission-${permission.id}`"
                                                    :modelValue="isPermissionSelected(permission.id)"
                                                    :binary="true"
                                                />
                                                <label class="text-sm cursor-pointer flex-1">
                                                    {{ permission.name }}
                                                </label>
                                            </div>
                                        </div>
                                    </AccordionContent>
                                </AccordionPanel>
                            </Accordion>
                        </div>
                        <InputError :message="form.errors.permissions" />
                    </div>

                    <div class="flex justify-end gap-2 mt-4">  
                        <Button 
                            type="button"
                            label="Cancel" 
                            severity="secondary"
                            outlined
                            @click="$inertia.visit(route('admin.roles.index'))"
                        />
                        <Button 
                            type="submit"
                            label="Create Role" 
                            :loading="form.processing"
                            :disabled="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>

<style scoped>
:deep(.p-accordionheader-toggle-icon) {
    background-color: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
    padding: 0.375rem;
    border-radius: 0.375rem;
    width: 1.75rem;
    height: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
