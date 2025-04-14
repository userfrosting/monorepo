import { describe, expect, beforeEach, afterEach, test, vi } from 'vitest'
import axios from 'axios'
import { useConfigStore } from '../../stores/config'
import { useCsrf } from '../../composables/useCsrf'
import { nextTick } from 'vue'

// Mock the config store
vi.mock('../../stores/config')
const mockUseConfigStore = {
    get: vi.fn()
}

describe('Csrf Composable', () => {
    afterEach(() => {
        vi.clearAllMocks()
        vi.resetAllMocks()
    })

    beforeEach(() => {
        // Mock the config store
        mockUseConfigStore.get.mockImplementation((key, defaultValue) => {
            if (key === 'csrf.enabled') return true
            if (key === 'csrf.name') return 'csrf'
            return defaultValue
        })
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        // Reset axios defaults
        axios.defaults.headers.post = {}
        axios.defaults.headers.put = {}
        axios.defaults.headers.delete = {}
        axios.defaults.headers.patch = {}

        // Reset the document head
        document.head.innerHTML = ''
    })

    test('initializes CSRF token name and value from meta tags', () => {
        document.head.innerHTML = `
            <meta name="csrf_name" content="123456">
            <meta name="csrf_value" content="7c4a8d09">
        `

        const { name, token } = useCsrf()
        expect(name.value).toBe('123456')
        expect(token.value).toBe('7c4a8d09')
    })

    test('sets axios headers correctly when CSRF is enabled', async () => {
        const csrf = useCsrf()

        // Expect axios default headers
        expect(axios.defaults.headers.post).toEqual({})
        expect(axios.defaults.headers.put).toEqual({})
        expect(axios.defaults.headers.delete).toEqual({})
        expect(axios.defaults.headers.patch).toEqual({})

        // Set CSRF token values - Will trigger the WatchEffect
        csrf.name.value = '654321'
        csrf.token.value = 'abcdef'

        // Wait for the next tick to ensure watchEffect is triggered
        await nextTick()

        // Expect axios headers to be set correctly
        expect(csrf.isEnabled()).toBe(true)
        expect(csrf.name.value).toBe('654321')
        expect(csrf.token.value).toBe('abcdef')
        expect(csrf.key_name.value).toBe('csrf_name')
        expect(csrf.key_value.value).toBe('csrf_value')
        expect(axios.defaults.headers.post['csrf_name']).toBe('654321')
        expect(axios.defaults.headers.post['csrf_value']).toBe('abcdef')
        expect(axios.defaults.headers.put['csrf_name']).toBe('654321')
        expect(axios.defaults.headers.put['csrf_value']).toBe('abcdef')
        expect(axios.defaults.headers.delete['csrf_name']).toBe('654321')
        expect(axios.defaults.headers.delete['csrf_value']).toBe('abcdef')
        expect(axios.defaults.headers.patch['csrf_name']).toBe('654321')
        expect(axios.defaults.headers.patch['csrf_value']).toBe('abcdef')
    })

    test('does not set axios headers when CSRF is disabled', () => {
        // Change the mock implementation to simulate CSRF being disabled
        mockUseConfigStore.get.mockImplementation((key, defaultValue) => {
            if (key === 'csrf.enabled') return false // CSRF is disabled
            if (key === 'csrf.name') return 'csrf'
            return defaultValue
        })
        vi.mocked(useConfigStore).mockReturnValue(mockUseConfigStore as any)

        // Get the CSRF composable
        const csrf = useCsrf()

        // Assert everything is empty
        expect(csrf.isEnabled()).toBe(false)
        expect(csrf.name.value).toBe('')
        expect(csrf.token.value).toBe('')
        expect(axios.defaults.headers.post).toEqual({})
        expect(axios.defaults.headers.put).toEqual({})
        expect(axios.defaults.headers.delete).toEqual({})
        expect(axios.defaults.headers.patch).toEqual({})
    })

    test('updates CSRF token updates meta tags', async () => {
        document.head.innerHTML = `
            <meta name="csrf_name" content="old_name">
            <meta name="csrf_value" content="old_value">
        `

        // Assert initial state
        const csrf = useCsrf()
        expect(csrf.name.value).toBe('old_name')
        expect(csrf.token.value).toBe('old_value')
        expect(document.querySelector("meta[name='csrf_name']")?.getAttribute('content')).toBe(
            'old_name'
        )
        expect(document.querySelector("meta[name='csrf_value']")?.getAttribute('content')).toBe(
            'old_value'
        )

        // Update CSRF tokens manually
        csrf.name.value = 'new_name'
        csrf.token.value = 'new_value'

        // Wait for the next tick to ensure watchEffect is triggered
        await nextTick()

        // Assert new state
        expect(csrf.name.value).toBe('new_name')
        expect(csrf.token.value).toBe('new_value')
        expect(document.querySelector("meta[name='csrf_name']")?.getAttribute('content')).toBe(
            'new_name'
        )
        expect(document.querySelector("meta[name='csrf_value']")?.getAttribute('content')).toBe(
            'new_value'
        )
    })

    test('CSRF token can be updated from headers', async () => {
        const csrf = useCsrf()

        // Assert initial state
        expect(csrf.name.value).toBe('')
        expect(csrf.token.value).toBe('')

        const headers = {
            'csrf-name': 'new_name',
            'csrf-value': 'new_value'
        }
        csrf.updateFromHeaders(headers)

        // Wait for the next tick to ensure watchEffect is triggered
        await nextTick()

        expect(csrf.name.value).toBe('new_name')
        expect(csrf.token.value).toBe('new_value')
        expect(document.querySelector("meta[name='csrf_name']")?.getAttribute('content')).toBe(
            'new_name'
        )
        expect(document.querySelector("meta[name='csrf_value']")?.getAttribute('content')).toBe(
            'new_value'
        )
        expect(axios.defaults.headers.post['csrf_name']).toBe('new_name')
        expect(axios.defaults.headers.post['csrf_value']).toBe('new_value')
        expect(axios.defaults.headers.put['csrf_name']).toBe('new_name')
        expect(axios.defaults.headers.put['csrf_value']).toBe('new_value')
        expect(axios.defaults.headers.delete['csrf_name']).toBe('new_name')
        expect(axios.defaults.headers.delete['csrf_value']).toBe('new_value')
        expect(axios.defaults.headers.patch['csrf_name']).toBe('new_name')
        expect(axios.defaults.headers.patch['csrf_value']).toBe('new_value')
    })

    test('CSRF token can handle empty headers', async () => {
        document.head.innerHTML = `
            <meta name="csrf_name" content="123456">
            <meta name="csrf_value" content="abcdef">
        `

        // Assert initial state
        const csrf = useCsrf()
        expect(csrf.name.value).toBe('123456')
        expect(csrf.token.value).toBe('abcdef')
        expect(document.querySelector("meta[name='csrf_name']")?.getAttribute('content')).toBe(
            '123456'
        )
        expect(document.querySelector("meta[name='csrf_value']")?.getAttribute('content')).toBe(
            'abcdef'
        )
        expect(axios.defaults.headers.post['csrf_name']).toBe('123456')
        expect(axios.defaults.headers.post['csrf_value']).toBe('abcdef')

        // Call updateFromHeaders with empty headers
        const headers = {
            foo: 'bar'
        }
        csrf.updateFromHeaders(headers)

        // Wait for the next tick to ensure watchEffect is triggered
        await nextTick()

        // Assert state remains unchanged
        expect(csrf.name.value).toBe('123456')
        expect(csrf.token.value).toBe('abcdef')
        expect(document.querySelector("meta[name='csrf_name']")?.getAttribute('content')).toBe(
            '123456'
        )
        expect(document.querySelector("meta[name='csrf_value']")?.getAttribute('content')).toBe(
            'abcdef'
        )
        expect(axios.defaults.headers.post['csrf_name']).toBe('123456')
        expect(axios.defaults.headers.post['csrf_value']).toBe('abcdef')
    })
})
