import { describe, expect, test, vi } from 'vitest'
import { createApp } from 'vue'
import plugins from '..'
import Components from '../plugins/components'
import * as AlertsStore from '@userfrosting/sprinkle-core/stores'

describe('plugins.ts', () => {
    test('should initiate the plugin', () => {
        const app = createApp({})

        vi.spyOn(Components, 'install')
        vi.spyOn(AlertsStore, 'useAlertsStore').mockReturnValue(vi.fn() as any)

        plugins.install(app)
        expect(Components.install).toHaveBeenCalled()
    })
})
