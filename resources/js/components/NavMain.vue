<script setup lang="ts">
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

// Check if any submenu item is active
const isSubmenuActive = (item: NavItem): boolean => {
    if (!item.items) return false;
    return item.items.some(subItem => subItem.href && urlIsActive(subItem.href, page.url));
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Menu item with submenu -->
                <SidebarMenuItem v-if="item.items && item.items.length > 0">
                    <Collapsible as-child :default-open="isSubmenuActive(item)">
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton
                                :is-active="isSubmenuActive(item)"
                                :tooltip="item.title"
                            >
                                <component v-if="item.icon" :is="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem
                                    v-for="subItem in item.items"
                                    :key="subItem.title"
                                >
                                    <SidebarMenuSubButton
                                        as-child
                                        :is-active="subItem.href ? urlIsActive(subItem.href, page.url) : false"
                                    >
                                        <Link :href="subItem.href || '#'">
                                            <component v-if="subItem.icon" :is="subItem.icon" />
                                            <span>{{ subItem.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </Collapsible>
                </SidebarMenuItem>

                <!-- Regular menu item without submenu -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton
                        v-if="item.href"
                        as-child
                        :is-active="urlIsActive(item.href, page.url)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component v-if="item.icon" :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
