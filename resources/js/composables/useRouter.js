import { router } from '@inertiajs/vue3';

/**
 * Route name to URL mapping
 * Add your routes here as you create them
 */
const routeMap = {
    'home': '/',
    'dashboard': '/dashboard',
    'admin.labels.index': '/admin/labels',
    'admin.labels.store': '/admin/labels',
    'admin.labels.create': '/admin/labels/create',
    'admin.labels.show': '/admin/labels/{id}',
    'admin.labels.edit': '/admin/labels/{id}/edit',
    'admin.labels.update': '/admin/labels/{id}',
    'admin.labels.destroy': '/admin/labels/{id}',
    'admin.users.index': '/admin/users',
    'admin.users.store': '/admin/users',
    'admin.users.create': '/admin/users/create',
    'admin.users.show': '/admin/users/{id}',
    'admin.users.edit': '/admin/users/{id}/edit',
    'admin.users.update': '/admin/users/{id}',
    'admin.users.destroy': '/admin/users/{id}',
    'admin.roles.index': '/admin/roles',
    'admin.roles.store': '/admin/roles',
    'admin.roles.create': '/admin/roles/create',
    'admin.roles.show': '/admin/roles/{id}',
    'admin.roles.edit': '/admin/roles/{id}/edit',
    'admin.roles.update': '/admin/roles/{id}',
    'admin.roles.destroy': '/admin/roles/{id}',
};

/**
 * Get URL for a named route
 * @param {string} name - Route name (e.g., 'admin.labels.index')
 * @param {number|string|object} params - Route parameters (id or object with params)
 * @returns {string} Route URL
 */
export function route(name, params = null) {
    let url = routeMap[name];
    
    if (!url) {
        // Fallback: try to construct from route name
        // Convert 'admin.labels.index' to '/admin/labels'
        url = name
            .replace(/\.index$/, '') // Remove .index suffix
            .replace(/\./g, '/'); // Replace dots with slashes
        
        url = `/${url}`;
    }
    
    // Handle parameters
    if (params !== null) {
        if (typeof params === 'number' || typeof params === 'string') {
            // Single parameter (usually an ID)
            // Check if URL has {id} placeholder
            if (url.includes('{id}')) {
                url = url.replace('{id}', params);
            } else {
                url = `${url}/${params}`;
            }
        } else if (typeof params === 'object') {
            // Multiple parameters
            Object.keys(params).forEach(key => {
                url = url.replace(`{${key}}`, params[key]);
            });
        }
    }
    
    return url;
}

// Extend the router object with route() method
router.route = route;

/**
 * Composable to use router with named route support
 * 
 * Usage:
 * const { route } = useRouter();
 * route('admin.labels.index') // returns '/admin/labels'
 * 
 * Or use router.route() directly:
 * router.route('admin.labels.index')
 */
export function useRouter() {
    return {
        router,
        route: route,
    };
}

