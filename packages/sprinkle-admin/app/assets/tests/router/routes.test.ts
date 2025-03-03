import { describe, expect, test } from 'vitest'

import AdminRoutes from '../../routes'

describe('routes.test.ts', () => {
    test('AdminRoutes should contain all the individual routes', () => {
        expect(AdminRoutes.length).toBe(8)
    })
})
