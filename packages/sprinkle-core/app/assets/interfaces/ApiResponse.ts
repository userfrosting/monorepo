/**
 * Interfaces - What the API expects and what it returns
 *
 * Generic API Response interface.
 */
export interface ApiResponse {
    title: string
    description?: string
}

export interface ApiErrorResponse {
    title: string
    description: string
    status: number
}
