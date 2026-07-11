import { beforeEach, describe, expect, test } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { Severity } from '../../interfaces'
import { useAlertsStore } from '../../stores/useAlertsStore'

describe('useAlertsStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    test('push adds alerts to the store', () => {
        const alertsStore = useAlertsStore()

        alertsStore.push({
            title: 'Alert title',
            description: 'Alert description',
            style: Severity.Info
        })

        expect(alertsStore.alerts).toHaveLength(1)
        expect(alertsStore.alerts[0].title).toBe('Alert title')
    })

    test('pop removes and returns the last alert', () => {
        const alertsStore = useAlertsStore()

        alertsStore.push({ title: 'First', style: Severity.Warning })
        alertsStore.push({ title: 'Second', style: Severity.Danger })

        const removed = alertsStore.pop()

        expect(removed?.title).toBe('Second')
        expect(alertsStore.alerts).toHaveLength(1)
        expect(alertsStore.alerts[0].title).toBe('First')
    })

    test('shift removes and returns the first alert', () => {
        const alertsStore = useAlertsStore()

        alertsStore.push({ title: 'First', style: Severity.Warning })
        alertsStore.push({ title: 'Second', style: Severity.Danger })

        const removed = alertsStore.shift()

        expect(removed?.title).toBe('First')
        expect(alertsStore.alerts).toHaveLength(1)
        expect(alertsStore.alerts[0].title).toBe('Second')
    })

    test('clear resets alerts collection', () => {
        const alertsStore = useAlertsStore()

        alertsStore.push({ title: 'First', style: Severity.Warning })
        alertsStore.push({ title: 'Second', style: Severity.Danger })

        alertsStore.clear()

        expect(alertsStore.alerts).toEqual([])
    })
})
