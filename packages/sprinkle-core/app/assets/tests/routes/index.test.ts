import { describe, expect, test } from 'vitest'
import routes from '../../routes'

describe('routes/index.ts', () => {
    test('defines all expected routes with metadata', () => {
        expect(routes).toHaveLength(4)

        expect(routes[0].name).toBe('NotFound')
        expect(routes[0].path).toBe('/:pathMatch(.*)*')
        expect(routes[0].meta).toEqual({
            title: 'ERROR.404.TITLE',
            description: 'ERROR.404.DESCRIPTION'
        })

        expect(routes[1].name).toBe('Unauthorized')
        expect(routes[1].meta).toEqual({
            title: 'ERROR.401.TITLE',
            description: 'ERROR.401.DESCRIPTION'
        })

        expect(routes[2].name).toBe('Forbidden')
        expect(routes[2].meta).toEqual({
            title: 'ERROR.403.TITLE',
            description: 'ERROR.403.DESCRIPTION'
        })

        expect(routes[3].name).toBe('Error')
        expect(routes[3].meta).toEqual({
            title: 'ERROR.TITLE',
            description: 'ERROR.DESCRIPTION'
        })
    })

    test('loads all lazy route components', async () => {
        const modules = await Promise.all(routes.map((route) => route.component?.()))

        for (const module of modules) {
            expect(module).toBeDefined()
            expect(module?.default).toBeDefined()
        }
    })
})
