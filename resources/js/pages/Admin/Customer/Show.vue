<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import { computed } from 'vue';
import { route } from '@/composables/useRouter';
import CustomerController from '@/actions/App/Http/Controllers/Admin/Customer/CustomerController.ts';

const props = defineProps({
    customer: {
        type: Object,
        required: true
    }
});

const breadcrumbItems = computed(() => [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Customers',
        href: CustomerController.index.url(),
    },
    {
        title: props.customer.name,
        href: CustomerController.show.url(props.customer.id),
    },
]);

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDateOnly = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusTag = computed(() => {
    return {
        label: props.customer.is_active ? 'Active' : 'Inactive',
        severity: props.customer.is_active ? 'success' : 'danger'
    };
});

const getVerifiedTag = computed(() => {
    return {
        label: props.customer.is_verified ? 'Verified' : 'Not Verified',
        severity: props.customer.is_verified ? 'success' : 'warning'
    };
});

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this customer?')) {
        router.delete(CustomerController.destroy.url(props.customer.id));
    }
};

const handleToggleStatus = () => {
    router.post(CustomerController.toggleStatus.url(props.customer.id), {}, {
        preserveScroll: true,
    });
};

const handleBack = () => {
    router.visit(CustomerController.index.url());
};

const handleEdit = () => {
    router.visit(CustomerController.edit.url(props.customer.id));
};
</script>

<template>
    <Head :title="customer.name" />

    <AppSidebarLayout :title="customer.name" :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div v-if="customer.avatar_url" class="h-20 w-20 rounded-full overflow-hidden border-2 border-border">
                        <img 
                            :src="customer.avatar_url" 
                            :alt="customer.name"
                            class="h-full w-full object-cover"
                            @error="$event.target.style.display = 'none'"
                        />
                    </div>
                    <div v-else class="flex h-20 w-20 items-center justify-center rounded-full bg-primary text-primary-foreground text-2xl font-bold">
                        {{ customer.name?.charAt(0)?.toUpperCase() || 'C' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-2xl font-bold">{{ customer.name }}</h1>
                            <Tag 
                                :value="getStatusTag.label" 
                                :severity="getStatusTag.severity"
                            />
                            <Tag 
                                :value="getVerifiedTag.label" 
                                :severity="getVerifiedTag.severity"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-muted-foreground" v-if="customer.email">
                                <i class="pi pi-envelope mr-1"></i>{{ customer.email }}
                            </p>
                            <p class="text-muted-foreground" v-if="customer.phone">
                                <i class="pi pi-phone mr-1"></i>{{ customer.phone }}
                            </p>
                            <p v-if="!customer.email && !customer.phone" class="text-muted-foreground">
                                No contact information
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button 
                        label="Back" 
                        icon="pi pi-arrow-left"
                        severity="secondary"
                        outlined
                        @click="handleBack"
                    />
                    <Button 
                        label="Edit" 
                        icon="pi pi-pencil"
                        severity="info"
                        @click="handleEdit"
                    />
                    <Button 
                        :label="customer.is_active ? 'Deactivate' : 'Activate'" 
                        :icon="customer.is_active ? 'pi pi-ban' : 'pi pi-check'"
                        :severity="customer.is_active ? 'warning' : 'success'"
                        outlined
                        @click="handleToggleStatus"
                    />
                    <Button 
                        label="Delete" 
                        icon="pi pi-trash"
                        severity="danger"
                        outlined
                        @click="handleDelete"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Customer Information -->
                <div class="rounded-lg border border-border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Full Name</label>
                            <p class="text-base">{{ customer.name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Email</label>
                            <p class="text-base">{{ customer.email || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Phone</label>
                            <p class="text-base">{{ customer.phone || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Date of Birth</label>
                            <p class="text-base">{{ formatDateOnly(customer.date_of_birth) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Gender</label>
                            <p class="text-base">{{ customer.gender ? customer.gender.charAt(0).toUpperCase() + customer.gender.slice(1) : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Account Status -->
                <div class="rounded-lg border border-border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Account Status</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Account Status</label>
                            <div class="mt-1">
                                <Tag 
                                    :value="getStatusTag.label" 
                                    :severity="getStatusTag.severity"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Verification Status</label>
                            <div class="mt-1">
                                <Tag 
                                    :value="getVerifiedTag.label" 
                                    :severity="getVerifiedTag.severity"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Email Verified</label>
                            <p class="text-base">{{ customer.email_verified_at ? formatDate(customer.email_verified_at) : 'Not verified' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Phone Verified</label>
                            <p class="text-base">{{ customer.phone_verified_at ? formatDate(customer.phone_verified_at) : 'Not verified' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Activity Information -->
                <div class="rounded-lg border border-border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Activity Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Customer ID</label>
                            <p class="text-base font-mono">{{ customer.id }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Registered At</label>
                            <p class="text-base">{{ formatDate(customer.created_at) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Last Updated</label>
                            <p class="text-base">{{ formatDate(customer.updated_at) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Last Login</label>
                            <p class="text-base">{{ customer.last_login_at ? formatDate(customer.last_login_at) : 'Never' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Last Login IP</label>
                            <p class="text-base font-mono">{{ customer.last_login_ip || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="rounded-lg border border-border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Additional Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Avatar</label>
                            <div class="mt-2">
                                <div v-if="customer.avatar_url" class="inline-block">
                                    <img 
                                        :src="customer.avatar_url" 
                                        :alt="customer.name + ' Avatar'"
                                        class="h-24 w-24 rounded-full object-cover border-2 border-border"
                                        @error="$event.target.style.display = 'none'"
                                    />
                                </div>
                                <p v-else class="text-base text-muted-foreground">No avatar uploaded</p>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Account Type</label>
                            <p class="text-base">
                                <Tag 
                                    :value="customer.email && customer.phone ? 'Email & Phone' : customer.email ? 'Email Only' : 'Phone Only'"
                                    severity="info"
                                />
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

