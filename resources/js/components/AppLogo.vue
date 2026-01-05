<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <div class="flex items-center gap-2">
        <img src="/images/logo.png" alt="Logo" class="h-8 w-auto object-contain" />
        <div class="ml-1 grid flex-1 text-left text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold">E-Commerce | {{ page.props.auth.user.name }}</span>
        </div>
    </div>
</template>
