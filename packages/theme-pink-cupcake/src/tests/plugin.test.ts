import { describe, expect, test, vi } from 'vitest'
import { createApp, ref } from 'vue'
import plugins from '..'
import Components from '../plugins/components'
import * as AlertsStore from '@userfrosting/sprinkle-core/stores'

describe('plugins.ts', () => {
    test('should initiate the plugin', () => {
        const app = createApp({})

        vi.spyOn(Components, 'install')
        vi.spyOn(AlertsStore, 'useAlertsStore').mockReturnValue({
            alerts: ref([]),
            addAlert: vi.fn(),
            removeAlert: vi.fn()
            // add any other properties/methods expected by the plugin
        } as any)

        plugins.install(app)
        expect(Components.install).toHaveBeenCalled()
    })
})
