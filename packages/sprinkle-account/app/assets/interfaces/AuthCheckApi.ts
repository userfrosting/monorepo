import type { UserDataInterface } from './'

/**
 * API Interfaces - What the API expects and what it returns
 *
 * This interface is tied to the `AuthCheckAction` API, accessed at the
 * POST `/account/auth` endpoint.
 *
 * N.B.: `user` will be null if the user is not authenticated. Same for the
 * `permission` key. The user object won't have any role or permissions.
 *
 * This api doesn't have a corresponding Request data interface.
 */
export interface AuthCheckResponse extends UserDataInterface {}
