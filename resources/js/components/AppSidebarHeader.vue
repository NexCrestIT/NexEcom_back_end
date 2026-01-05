<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';

withDefaults(
    defineProps<{
        title?: string;
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        title: '',
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-4 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <!-- Left side: Sidebar trigger and title -->
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <h1 v-if="title" class="text-lg font-semibold">
                {{ title }}
            </h1>
        </div>

        <!-- Right side: Breadcrumbs -->
        <div class="flex items-center">
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
    </header>
</template>
