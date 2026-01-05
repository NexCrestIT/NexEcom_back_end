import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
    exact: boolean = false,
) {
    const url = toUrl(urlToCheck);
    if (exact) {
        return url === currentUrl;
    }
    // For nested routes, check if current URL starts with the menu URL
    // This ensures /admin/categories/1/edit matches /admin/categories
    if (url === currentUrl) {
        return true;
    }
    // Remove trailing slashes for comparison
    const normalizedUrl = url.replace(/\/$/, '');
    const normalizedCurrent = currentUrl.replace(/\/$/, '');
    // Check if current URL starts with the menu URL (for nested routes)
    return normalizedCurrent.startsWith(normalizedUrl + '/') || normalizedCurrent === normalizedUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
