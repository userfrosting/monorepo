import type { ApiResponse } from '@userfrosting/sprinkle-core/interfaces'

/**
 * Interfaces - What the API expects and what it returns
 */
export interface GroupCreateRequest {
    slug: string
    name: string
    description: string
    icon: string
}

export type GroupCreateResponse = ApiResponse
