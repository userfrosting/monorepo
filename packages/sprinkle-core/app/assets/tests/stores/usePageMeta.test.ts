import { beforeEach, describe, expect, test, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { reactive, nextTick } from 'vue'
import { useConfigStore } from '../../stores/useConfigStore'
import { usePageMeta } from '../../stores/usePageMeta'

const route = reactive<any>({
    matched: [],
    meta: {},
    path: '/'
})

vi.mock('vue-router', () => ({
    useRoute: () => route
}))

vi.mock('../../stores/useTranslator', () => ({
    useTranslator: () => ({
        translate: (key: string) => `t:${key}`
    })
}))

describe('usePageMeta', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
        document.head.innerHTML = '<meta name="description" content="">'

        const configStore = useConfigStore()
        configStore.config = {
            site: {
                title: 'Site Title'
            }
        }

        route.path = '/users'
        route.meta = {
            title: 'PAGE.USERS.TITLE',
            description: 'PAGE.USERS.DESCRIPTION'
        }
        route.matched = [
            { path: '/hidden', meta: { title: '', description: '' } },
            { path: '/users', meta: { title: 'PAGE.USERS.TITLE', description: '' } },
            { path: '/users/view', meta: { title: 'PAGE.USERS.VIEW.TITLE', description: '' } }
        ]
    })

    test('builds breadcrumbs and updates document metadata on initialization', () => {
        const pageMeta = usePageMeta()

        expect(pageMeta.title).toBe('PAGE.USERS.TITLE')
        expect(pageMeta.description).toBe('t:PAGE.USERS.DESCRIPTION')

        expect(pageMeta.breadcrumbs).toEqual([
            { label: 'Site Title', to: '/' },
            { label: 'PAGE.USERS.TITLE', to: '/users' },
            { label: 'PAGE.USERS.VIEW.TITLE', to: '/users/view' }
        ])

        expect(document.title).toBe('t:PAGE.USERS.TITLE | Site Title')
        expect(
            document.querySelector('head meta[name="description"]')?.getAttribute('content')
        ).toBe('t:PAGE.USERS.DESCRIPTION')
    })

    test('refreshes title, breadcrumbs and description on route updates', async () => {
        const pageMeta = usePageMeta()

        route.path = '/settings'
        route.meta = {
            title: 'PAGE.SETTINGS.TITLE',
            description: 'PAGE.SETTINGS.DESCRIPTION'
        }
        route.matched = [{ path: '/settings', meta: { title: 'PAGE.SETTINGS.TITLE' } }]

        await nextTick()

        expect(pageMeta.title).toBe('PAGE.SETTINGS.TITLE')
        expect(pageMeta.description).toBe('t:PAGE.SETTINGS.DESCRIPTION')
        expect(pageMeta.breadcrumbs).toEqual([
            { label: 'Site Title', to: '/' },
            { label: 'PAGE.SETTINGS.TITLE', to: '/settings' }
        ])
        expect(document.title).toBe('t:PAGE.SETTINGS.TITLE | Site Title')
        expect(
            document.querySelector('head meta[name="description"]')?.getAttribute('content')
        ).toBe('t:PAGE.SETTINGS.DESCRIPTION')
    })

    test('resets visibility flags on refresh', async () => {
        const pageMeta = usePageMeta()

        pageMeta.hideBreadcrumbs = true
        pageMeta.hideTitle = true

        route.meta = {
            title: 'PAGE.PROFILE.TITLE',
            description: 'PAGE.PROFILE.DESCRIPTION'
        }

        await nextTick()

        expect(pageMeta.hideBreadcrumbs).toBe(false)
        expect(pageMeta.hideTitle).toBe(false)
    })

    test('uses site title when current route title is empty', async () => {
        const pageMeta = usePageMeta()

        route.meta = {
            title: '',
            description: ''
        }
        route.matched = [
            { path: '/settings', meta: { title: undefined } },
            { path: '/settings/profile', meta: { title: 'PAGE.PROFILE.TITLE' } }
        ]

        await nextTick()

        expect(pageMeta.title).toBe('')
        expect(document.title).toBe('Site Title')
        expect(pageMeta.breadcrumbs).toEqual([
            { label: 'Site Title', to: '/' },
            { label: 'PAGE.PROFILE.TITLE', to: '/settings/profile' }
        ])
    })

    test('skips description update when description meta tag is missing', async () => {
        document.head.innerHTML = ''

        const pageMeta = usePageMeta()

        route.meta = {
            title: 'PAGE.ABOUT.TITLE',
            description: 'PAGE.ABOUT.DESCRIPTION'
        }

        await nextTick()

        expect(pageMeta.description).toBe('t:PAGE.ABOUT.DESCRIPTION')
        expect(document.querySelector('head meta[name="description"]')).toBeNull()
    })

    test('falls back to empty site title and breadcrumb label fallback path', async () => {
        const configStore = useConfigStore()
        configStore.config = {}

        const pageMeta = usePageMeta()

        route.meta = {
            title: 'PAGE.ODD.TITLE',
            description: 'PAGE.ODD.DESCRIPTION'
        }
        route.matched = [{ path: '/odd', meta: { title: 0 as any } }]

        await nextTick()

        expect(pageMeta.breadcrumbs).toEqual([
            { label: '', to: '/' },
            { label: '', to: '/odd' }
        ])
        expect(document.title).toBe('t:PAGE.ODD.TITLE |')
    })
})
