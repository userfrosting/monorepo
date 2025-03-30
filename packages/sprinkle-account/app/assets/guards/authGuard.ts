import { watchEffect } from 'vue'
import { useAuthStore } from '../stores/auth'
import type { RouteLocationRaw, Router } from 'vue-router'

export function useAuthGuard(router: Router) {
    const auth = useAuthStore()

    /**
     * Return the auth RouteGuard
     */
    const getRouteAuth = () => {
        return router.currentRoute.value.meta.auth ?? null
    }

    /**
     * Return the auth RouteGuard
     */
    const getRoutePermission = () => {
        return router.currentRoute.value.meta.permission ?? null
    }

    /**
     * Return the guest RouteGuard
     */
    const getRouteGuest = () => {
        return router.currentRoute.value.meta.guest ?? null
    }

    /**
     * Apply auth route guard
     */
    const applyAuthGuard = () => {
        const authGuard = getRouteAuth()
        if (authGuard !== null && !auth.isAuthenticated) {
            const redirectTo = authGuard.redirect ?? getErrorRoute('Unauthorized')
            redirect(redirectTo)
        }
    }

    /**
     * Apply permission route guard
     */
    const applyPermissionGuard = () => {
        const permissionGuard = getRoutePermission()
        if (
            auth.isAuthenticated &&
            permissionGuard?.slug !== undefined &&
            !auth.checkAccess(permissionGuard.slug)
        ) {
            const redirectTo = permissionGuard.redirect ?? getErrorRoute('Forbidden')
            redirect(redirectTo)
        }
    }

    /**
     * Apply guest route guard
     */
    const applyGuestGuard = () => {
        const guestGuard = getRouteGuest()
        if (guestGuard !== null && auth.isAuthenticated) {
            const redirectTo = guestGuard.redirect ?? getErrorRoute('Unauthorized')
            redirect(redirectTo)
        }
    }

    /**
     * Redirect to the specified route
     */
    const redirect = (redirectTo: RouteLocationRaw) => {
        router.replace(redirectTo)
    }

    /**
     * Get the error route location object for the specified route name.
     *
     * N.B.: Param is used to preserver the current path. Substring is used to
     * remove the first char to avoid the target URL starting with `//`. Query
     * and hash are used to preserve the current query and hash.
     * @see https://router.vuejs.org/guide/essentials/dynamic-matching.html#Catch-all-404-Not-found-Route
     */
    const getErrorRoute = (name: string) => {
        return {
            name: name,
            params: { pathMatch: router.currentRoute.value.path.substring(1).split('/') },
            query: router.currentRoute.value.query,
            hash: router.currentRoute.value.hash
        }
    }

    watchEffect(() => {
        applyAuthGuard()
        applyPermissionGuard()
        applyGuestGuard()
    })
}
