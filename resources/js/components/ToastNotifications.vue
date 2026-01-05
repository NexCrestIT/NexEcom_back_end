<script setup lang="ts">
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface ToastData {
    type: 'success' | 'error' | 'info' | 'warn';
    title: string;
    message: string;
}

const toast = useToast();
const page = usePage();

// Watch for toast messages in page props
watch(
    () => (page.props as any).toast,
    (toastData: ToastData | null) => {
        if (toastData) {
            toast.add({
                severity: toastData.type,
                summary: toastData.title,
                detail: toastData.message,
                life: 3000,
            });
        }
    },
    { deep: true }
);
</script>

<template>
    <Toast />
</template>

