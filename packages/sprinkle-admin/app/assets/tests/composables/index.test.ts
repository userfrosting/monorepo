import { describe, expect, test } from 'vitest'
import * as composables from '../../composables'

describe('composables/index.ts', () => {
    test('exports all expected composables', () => {
        expect(composables.useDashboardApi).toBeDefined()
        expect(composables.useConfigCacheApi).toBeDefined()
        expect(composables.useConfigSystemInfoApi).toBeDefined()
        expect(composables.useGroupApi).toBeDefined()
        expect(composables.useGroupsApi).toBeDefined()
        expect(composables.usePermissionApi).toBeDefined()
        expect(composables.useRoleApi).toBeDefined()
        expect(composables.useRoleUpdateApi).toBeDefined()
        expect(composables.useRolePermissionsApi).toBeDefined()
        expect(composables.useUserApi).toBeDefined()
        expect(composables.useUserUpdateApi).toBeDefined()
        expect(composables.useUserPasswordResetApi).toBeDefined()
        expect(composables.useUserRolesApi).toBeDefined()
    })
})
