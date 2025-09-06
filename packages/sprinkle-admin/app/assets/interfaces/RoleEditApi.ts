import type { RoleCreateRequest, RoleCreateResponse } from './RoleCreateApi'

/**
 * API Interfaces - What the API expects and what it returns
 *
 * This interface is tied to the `RoleEditAction` API, accessed at the
 * PUT `/api/roles/r/:slug` endpoint.
 */
export type RoleEditRequest = RoleCreateRequest
export type RoleEditResponse = RoleCreateResponse
