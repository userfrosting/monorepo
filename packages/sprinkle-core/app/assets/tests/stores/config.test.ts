import { describe, expect, beforeEach, test, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import axios from 'axios'
import { useConfigStore } from '../../stores/useConfigStore'

const testConfig = {
    name: 'Test Config',
    description: 'Test description',
    api: {
        url: 'https://api.example.com',
        version: '1.0'
    }
}

describe('Config Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    test('should load config data', async () => {
        // Arrange
        const configStore = useConfigStore()
        const response = { data: testConfig }
        vi.spyOn(axios, 'get').mockResolvedValue(response as any)

        // Assert initial state
        expect(configStore.config).toEqual({})

        // Act
        await configStore.load()

        // Assert
        expect(axios.get).toHaveBeenCalledWith('/api/config')
        expect(configStore.config).toStrictEqual(testConfig)

        // Assert get method
        expect(configStore.get('name')).toBe('Test Config')
        expect(configStore.get('api.url')).toBe('https://api.example.com')
        expect(configStore.get('api.version', '0.0')).toBe('1.0')
        expect(configStore.get('api.key', 'API_KEY')).toBe('API_KEY')
    })
})
