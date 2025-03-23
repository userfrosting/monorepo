import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import type { UserDataInterface } from '../interfaces'

/**
 * API Composable
 */
export function useAuthorizationManager(user: UserDataInterface | null) {
    function checkAccess(slug: string): Boolean {
        // Trace debug information
        debugAuth(`==> Checking authorization access for ${user?.user_name}`, {
            user,
            slug
        })

        // Deny access if no user is defined.
        if (user === null) {
            debugAuth('No user defined. Access denied.')

            return false
        }

        // The master (root) account has access to everything.
        if (user.is_master) {
            debugAuth('User is the master (root) user. Access granted.')

            return true
        }

        // Find all permissions that apply to this user (via roles), and check if any evaluate to true.
        if (user.permissions === null || user.permissions[slug] === undefined) {
            debugAuth('No permissions found. Access denied.')

            return false
        }

        // Find matching permission conditions
        const conditions = user.permissions[slug]
        debugAuth('Found matching permission conditions', conditions)

        // Check if any condition is 'always()'
        if (conditions.some((condition) => condition === 'always()')) {
            debugAuth(`User passed conditions 'always()'. Access granted.`)

            return true
        } else if (conditions.length !== 0) {
            // We don't support other condition than always(). Log a warning and don't accept.
            debugAuth(`Unsupported conditions found. Only 'always()' is supported.`)
        }

        debugAuth('User failed to pass any of the matched permissions. Access denied.')

        return false
    }

    function debugAuth(message: string, payload?: any): void {
        const config = useConfigStore()
        if (config.get('site.debug.auth', false)) {
            console.debug(message, payload)
        }
    }

    return {
        checkAccess
    }
}
