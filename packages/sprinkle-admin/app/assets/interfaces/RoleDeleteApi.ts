import type { ApiResponse } from '@userfrosting/sprinkle-core/interfaces'

/**
 * Api Interface - What the API expects and what it returns
 *
 * This interface is tied to the `RoleDeleteAction` API, accessed at the
 * DELETE `/api/roles/r/:slug` endpoint.
 *
 * This api doesn't have a corresponding Request data interface.
 */
export type RoleDeleteResponse = ApiResponse
