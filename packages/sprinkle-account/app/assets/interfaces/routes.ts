/**
 * Interface for custom routes meta fields.
 *
 * Meta Fields Added :
 * - auth: RouteGuard - Guard for authenticated users
 * - guest: RouteGuard - Guard for not authenticated users
 *
 * @see https://router.vuejs.org/guide/advanced/meta.html#TypeScript
 */
import 'vue-router'

export interface RouteGuard {
    redirect?: string | { name: string }
}

export interface RoutePermissionGuard extends RouteGuard {
    slug: string
}

declare module 'vue-router' {
    interface RouteMeta {
        auth?: RouteGuard
        guest?: RouteGuard
        permission?: RoutePermissionGuard
    }
}
