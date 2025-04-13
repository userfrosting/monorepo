import { describe, expect, test, vi } from 'vitest'
import { createApp } from 'vue'
import { useConfigStore } from '../stores/config'
import plugin from '..'
import * as Config from '../stores/config'
import * as Translator from '../stores/useTranslator'

const mockConfigStore = {
    load: vi.fn(),
    get: vi.fn().mockReturnValue('csrf')
}

const mockTranslatorStore = {
    load: vi.fn()
}

describe('Plugin', () => {
    test('should install the plugin and initiate load', () => {
        const app = createApp({})

        vi.spyOn(Config, 'useConfigStore').mockReturnValue(mockConfigStore as any)
        vi.spyOn(Translator, 'useTranslator').mockReturnValue(mockTranslatorStore as any)

        plugin.install(app)

        expect(useConfigStore).toHaveBeenCalled()
        expect(mockConfigStore.load).toHaveBeenCalled()
    })
})
