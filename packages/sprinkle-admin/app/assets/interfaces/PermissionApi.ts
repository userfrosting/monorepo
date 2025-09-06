import type { PermissionInterface } from '@userfrosting/sprinkle-account/interfaces'

/**
 * API Interfaces - What the API expects and what it returns
 *
 * This interface is tied to the `PermissionApi` API, accessed at the
 * GET `/api/permissions/p/{id}` endpoint.
 *
 * This api doesn't have a corresponding Request data interface.
 */
export type PermissionResponse = PermissionInterface
