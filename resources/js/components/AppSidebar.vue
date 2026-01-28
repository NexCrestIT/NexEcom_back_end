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
import { BookOpen, Folder, LayoutGrid, Users, Shield, Tag, UserCog, UserCircle, ShoppingCart } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';
import AttributeController from '@/actions/App/Http/Controllers/Admin/Attribute/AttributeController.ts';
import BrandController from '@/actions/App/Http/Controllers/Admin/Brand/BrandController.ts';
import CarouselController from '@/actions/App/Http/Controllers/Admin/Carousel/CarouselController.ts';
import CategoryController from '@/actions/App/Http/Controllers/Admin/Category/CategoryController.ts';
import CollectionController from '@/actions/App/Http/Controllers/Admin/Collection/CollectionController.ts';
import DiscountController from '@/actions/App/Http/Controllers/Admin/Discount/DiscountController.ts';
import FlashSaleController from '@/actions/App/Http/Controllers/Admin/FlashSale/FlashSaleController.ts';
import GenderController from '@/actions/App/Http/Controllers/Admin/Gender/GenderController.ts';
import InventoryController from '@/actions/App/Http/Controllers/Admin/Inventory/InventoryController.ts';
import ScentFamilyController from '@/actions/App/Http/Controllers/Admin/ScentFamily/ScentFamilyController.ts';
import LabelController from '@/actions/App/Http/Controllers/Admin/Label/LabelController.ts';
import PriceListController from '@/actions/App/Http/Controllers/Admin/Price/PriceListController.ts';
import OptionController from '@/actions/App/Http/Controllers/Admin/Option/OptionController.ts';
import ProductController from '@/actions/App/Http/Controllers/Admin/Product/ProductController.ts';
import TagController from '@/actions/App/Http/Controllers/Admin/Tag/TagController.ts';
import UserController from '@/actions/App/Http/Controllers/Admin/User/UserController.ts';
import RoleController from '@/actions/App/Http/Controllers/Admin/Role/RoleController.ts';
import CustomerController from '@/actions/App/Http/Controllers/Admin/Customer/CustomerController.ts';
import OrderController from '@/actions/App/Http/Controllers/Admin/Order/OrderController.ts';

const mainNavItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
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
    {
        title: 'Customers',
        href: CustomerController.index.url(),
        icon: UserCircle,
    },
    {
        title: 'Orders',
        href: OrderController.index.url(),
        icon: ShoppingCart,
    },
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
        title: 'Scent Families',
        href: ScentFamilyController.index.url(),
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
    {
        title: 'Inventory',
        href: InventoryController.index.url(),
        icon: Folder,
    },
    {
        title: 'Carousels',
        href: CarouselController.index.url(),
        icon: Tag,
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
