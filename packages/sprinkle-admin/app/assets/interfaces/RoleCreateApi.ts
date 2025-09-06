import type { ApiResponse } from '@userfrosting/sprinkle-core/interfaces'

/**
 * API Interfaces - What the API expects and what it returns
 *
 * This interface is tied to the `RoleCreateAction` API, accessed at the
 * POST `/api/roles` endpoint.
 */
export interface RoleCreateRequest {
    name: string
    slug: string
    description: string
}

export type RoleCreateResponse = ApiResponse
