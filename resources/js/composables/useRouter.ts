import { router } from '@inertiajs/vue3';

/**
 * Route name to URL mapping
 * Add your routes here as you create them
 */
const routeMap: Record<string, string> = {
    'home': '/',
    'dashboard': '/dashboard',
    'admin.labels.index': '/admin/labels',
    'admin.labels.store': '/admin/labels',
    'admin.users.index': '/admin/users',
    'admin.users.create': '/admin/users/create',
    'admin.users.store': '/admin/users',
    'admin.users.show': '/admin/users/:id',
    'admin.users.edit': '/admin/users/:id/edit',
    'admin.users.update': '/admin/users/:id',
    'admin.users.destroy': '/admin/users/:id',
    'admin.roles.index': '/admin/roles',
    'admin.roles.create': '/admin/roles/create',
    'admin.roles.store': '/admin/roles',
    'admin.roles.show': '/admin/roles/:id',
    'admin.roles.edit': '/admin/roles/:id/edit',
    'admin.roles.update': '/admin/roles/:id',
    'admin.roles.destroy': '/admin/roles/:id',
    'admin.categories.index': '/admin/categories',
    'admin.categories.create': '/admin/categories/create',
    'admin.categories.store': '/admin/categories',
    'admin.categories.show': '/admin/categories/:id',
    'admin.categories.edit': '/admin/categories/:id/edit',
    'admin.categories.update': '/admin/categories/:id',
    'admin.categories.destroy': '/admin/categories/:id',
    'admin.categories.toggle-status': '/admin/categories/:id/toggle-status',
    'admin.categories.toggle-featured': '/admin/categories/:id/toggle-featured',
    // Add more routes as needed
};

/**
 * Get URL for a named route with optional parameters
 * @param name - Route name (e.g., 'admin.categories.show')
 * @param params - Route parameters (can be a single value or an object)
 * @returns Route URL
 */
function getRouteUrl(name: string, params?: string | number | Record<string, any>): string {
    let url = routeMap[name];
    
    if (!url) {
        // Fallback: try to construct from route name
        // Convert 'admin.labels.index' to '/admin/labels'
        url = name
            .replace(/\.index$/, '') // Remove .index suffix
            .replace(/\.store$/, '') // Remove .store suffix
            .replace(/\.create$/, '/create') // Handle create
            .replace(/\.show$/, '/:id') // Handle show
            .replace(/\.edit$/, '/:id/edit') // Handle edit
            .replace(/\.update$/, '/:id') // Handle update
            .replace(/\.destroy$/, '/:id') // Handle destroy
            .replace(/\./g, '/'); // Replace remaining dots with slashes
        
        url = `/${url}`;
    }
    
    // Replace route parameters
    if (params !== undefined && params !== null) {
        if (typeof params === 'object' && !Array.isArray(params)) {
            // Handle object parameters like { id: 1, slug: 'test' }
            Object.keys(params).forEach(key => {
                url = url.replace(`:${key}`, String(params[key]));
            });
        } else {
            // Handle single parameter (most common case)
            url = url.replace(':id', String(params));
        }
    }
    
    return url;
}

// Extend the router object with route() method
(router as any).route = getRouteUrl;

/**
 * Composable to use router with named route support
 * 
 * Usage:
 * const { route } = useRouter();
 * route('admin.categories.index') // returns '/admin/categories'
 * route('admin.categories.show', 1) // returns '/admin/categories/1'
 * route('admin.categories.edit', 1) // returns '/admin/categories/1/edit'
 * 
 * Or use router.route() directly:
 * router.route('admin.categories.index')
 */
export function useRouter() {
    return {
        router,
        route: getRouteUrl,
    };
}

/**
 * Export route function directly for convenience
 */
export const route = getRouteUrl;

