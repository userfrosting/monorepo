import type { ApiResponse } from '@userfrosting/sprinkle-core/interfaces'

/**
 * Interfaces - What the API expects and what it returns
 */
export interface GroupEditRequest {
    slug: string
    name: string
    description: string
    icon: string
}

export type GroupEditResponse = ApiResponse
