import type { UserInterface, UserPermissionsMapInterface } from './'

/**
 * Special user interface that includes permissions and master status.
 * This interface is used by the auth store to store the current user with extra
 * information needed for authorization.
 */
export interface UserDataInterface extends UserInterface {
    permissions: UserPermissionsMapInterface
    is_master: boolean
}
