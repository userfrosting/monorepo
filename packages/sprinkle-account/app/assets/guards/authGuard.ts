import { watchEffect } from 'vue'
import { useAuthStore } from '../stores/useAuthStore'
import type { Router } from 'vue-router'

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
            const redirectTo = authGuard.redirect ?? getLoginRoute()
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
            const redirectTo = getGuestRedirectRoute(guestGuard.redirect)
            redirect(redirectTo)
        }
    }

    /**
     * Redirect to the specified route
     */
    const redirect = (redirectTo: any) => {
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

    /**
     * Get the login route location object, adding the current path to the
     * redirect query parameter. This is used to redirect the user to the
     * login page if they are not authenticated.
     */
    const getLoginRoute = () => {
        return {
            name: 'account.login',
            params: router.currentRoute.value.params,
            query: {
                ...router.currentRoute.value.query,
                ...{ redirect: router.currentRoute.value.path }
            },
            hash: router.currentRoute.value.hash
        }
    }

    /**
     * If a redirect is set in the url query, redirect to it
     *
     * Get the redirect route location object, adding the current path to the
     * redirect query parameter. This is used to redirect the user to the
     * login page if they are not authenticated.
     */
    const getGuestRedirectRoute = (definedRouteName: string | { name: string } | undefined) => {
        if (router.currentRoute.value.query?.redirect !== undefined) {
            // If a redirect is set in the url query, it has priority
            // Remove the redirect query from the query
            const path = router.currentRoute.value.query.redirect?.toString()
            delete router.currentRoute.value.query.redirect
            return {
                path: path,
                params: router.currentRoute.value.params,
                query: router.currentRoute.value.query,
                hash: router.currentRoute.value.hash
            }
        } else if (definedRouteName !== undefined) {
            // If the guard has a redirect, use it
            if (typeof definedRouteName === 'object' && definedRouteName.name) {
                return {
                    name: definedRouteName.name,
                    params: router.currentRoute.value.params,
                    query: router.currentRoute.value.query,
                    hash: router.currentRoute.value.hash
                }
            } else {
                return {
                    path: definedRouteName,
                    params: router.currentRoute.value.params,
                    query: router.currentRoute.value.query,
                    hash: router.currentRoute.value.hash
                }
            }
        } else {
            // Last resort, redirect to an error page
            return getErrorRoute('Unauthorized')
        }
    }

    watchEffect(() => {
        applyAuthGuard()
        applyPermissionGuard()
        applyGuestGuard()
    })
}
