import { describe, expect, test } from 'vitest'
import routes from '../../routes'

describe('routes/index.ts', () => {
    test('defines expected account routes and metadata', () => {
        expect(routes).toHaveLength(5)

        expect(routes[0].name).toBe('account.login')
        expect(routes[0].path).toBe('/account/sign-in')
        expect(routes[0].meta).toEqual({
            guest: {
                redirect: { name: 'home' }
            },
            title: 'LOGIN',
            description: 'LOGIN.PAGE'
        })

        expect(routes[1].name).toBe('account.register')
        expect(routes[2].name).toBe('account.forgot-password')
        expect(routes[3].name).toBe('account.verification')

        expect(routes[4].name).toBe('account.settings')
        expect(routes[4].redirect).toEqual({ name: 'account.settings.profile' })
        expect(routes[4].children).toHaveLength(3)
        expect(routes[4].children?.[0].name).toBe('account.settings.profile')
        expect(routes[4].children?.[1].name).toBe('account.settings.password')
        expect(routes[4].children?.[2].name).toBe('account.settings.email')
    })

    test('loads all lazy route components', async () => {
        const loaders = [
            routes[0].component,
            routes[1].component,
            routes[2].component,
            routes[3].component,
            routes[4].component,
            routes[4].children?.[0].component,
            routes[4].children?.[1].component,
            routes[4].children?.[2].component
        ].filter(Boolean)

        const modules = await Promise.all(loaders.map((loader) => loader?.()))

        for (const module of modules) {
            expect(module).toBeDefined()
            expect(module?.default).toBeDefined()
        }
    })
})
