<script setup>
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, Shield, UserCog, FolderTree, Tag } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';
import AttributeController from '@/actions/App/Http/Controllers/Admin/Attribute/AttributeController';
import BrandController from '@/actions/App/Http/Controllers/Admin/Brand/BrandController';
import CategoryController from '@/actions/App/Http/Controllers/Admin/Category/CategoryController';
import CollectionController from '@/actions/App/Http/Controllers/Admin/Collection/CollectionController';
import DiscountController from '@/actions/App/Http/Controllers/Admin/Discount/DiscountController';
import FlashSaleController from '@/actions/App/Http/Controllers/Admin/FlashSale/FlashSaleController';
import GenderController from '@/actions/App/Http/Controllers/Admin/Gender/GenderController';
import InventoryController from '@/actions/App/Http/Controllers/Admin/Inventory/InventoryController';
import LabelController from '@/actions/App/Http/Controllers/Admin/Label/LabelController';
import PriceListController from '@/actions/App/Http/Controllers/Admin/Price/PriceListController';
import OptionController from '@/actions/App/Http/Controllers/Admin/Option/OptionController';
import ProductController from '@/actions/App/Http/Controllers/Admin/Product/ProductController';
import TagController from '@/actions/App/Http/Controllers/Admin/Tag/TagController';
import UserController from '@/actions/App/Http/Controllers/Admin/User/UserController';
import RoleController from '@/actions/App/Http/Controllers/Admin/Role/RoleController';

const mainNavItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Catalog',
        icon: FolderTree,
        items: [
            {
                title: 'Categories',
                href: CategoryController.index.url(),
                icon: Folder,
            },
            {
                title: 'Attributes',
                href: AttributeController.index.url(),
                icon: Tag,
    },
            {
                title: 'Labels',
                href: LabelController.index.url(),
                icon: Tag,
            },
            {
                title: 'Tags',
                href: TagController.index.url(),
                icon: Tag,
            },
            {
                title: 'Genders',
                href: GenderController.index.url(),
                icon: Tag,
            },
            {
                title: 'Brands',
                href: BrandController.index.url(),
                icon: Tag,
            },
            {
                title: 'Collections',
                href: CollectionController.index.url(),
                icon: Tag,
            },
            {
                title: 'Options',
                href: OptionController.index.url(),
                icon: Tag,
            },
            {
                title: 'Discounts',
                href: DiscountController.index.url(),
                icon: Tag,
            },
            {
                title: 'Flash Sales',
                href: FlashSaleController.index.url(),
                icon: Tag,
            },
            {
                title: 'Products',
                href: ProductController.index.url(),
                icon: Tag,
            },
            {
                title: 'Price Lists',
                href: PriceListController.index.url(),
                icon: Tag,
            },
        ],
    },
    {
        title: 'Inventory',
        icon: Folder,
        items: [
            {
                title: 'Inventory',
                href: InventoryController.index.url(),
                icon: Folder,
            },
        ],
    },
    {
        title: 'User Management',
        icon: UserCog,
        items: [
            {
                title: 'Users',
                href: UserController.index.url(),
                icon: Users,
            },
            {
                title: 'Roles',
                href: RoleController.index.url(),
                icon: Shield,
            },
        ],
    },
]);

const footerNavItems = computed(() => [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="[]" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
