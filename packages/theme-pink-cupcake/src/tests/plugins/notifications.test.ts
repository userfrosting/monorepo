import { describe, expect, test, vi } from 'vitest'
import { ref, nextTick } from 'vue'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import NotificationsPlugin from '../../plugins/notifications'

const { alertsHolder, shift, notification } = vi.hoisted(() => ({
    alertsHolder: { current: null as any },
    shift: vi.fn(),
    notification: vi.fn()
}))

const alerts = ref<any[]>([])
alertsHolder.current = alerts

vi.mock('uikit', () => ({
    default: {
        notification
    }
}))

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useAlertsStore: () => ({
        shift
    })
}))

vi.mock('pinia', () => ({
    storeToRefs: () => ({ alerts: alertsHolder.current })
}))

describe('notifications plugin', () => {
    test('drains alerts and maps severities to uikit notifications', async () => {
        shift
            .mockReturnValueOnce({ title: 'Hello', description: 'World', style: Severity.Success })
            .mockReturnValueOnce({ title: 'Warn', style: Severity.Warning })
            .mockReturnValueOnce({ description: 'Only description', style: Severity.Muted })
            .mockReturnValueOnce(undefined)

        NotificationsPlugin.install()
        alerts.value = [{}, {}, {}]
        await nextTick()

        expect(notification).toHaveBeenNthCalledWith(1, {
            message: '<strong>Hello</strong>: World',
            status: 'success',
            pos: 'top-right',
            timeout: 4000
        })
        expect(notification).toHaveBeenNthCalledWith(2, {
            message: 'Warn',
            status: 'warning',
            pos: 'top-right',
            timeout: 4000
        })
        expect(notification).toHaveBeenNthCalledWith(3, {
            message: 'Only description',
            status: undefined,
            pos: 'top-right',
            timeout: 4000
        })
    })

    test('uses primary status for default and info for info severity', async () => {
        shift.mockReset()
        notification.mockReset()
        alerts.value = []

        shift
            .mockReturnValueOnce({ title: 'Default', style: Severity.Default })
            .mockReturnValueOnce({ title: 'Info', style: Severity.Info })
            .mockReturnValueOnce(undefined)

        NotificationsPlugin.install()
        alerts.value = [{}, {}]
        await nextTick()

        expect(notification).toHaveBeenNthCalledWith(1, expect.objectContaining({ status: 'primary' }))
        expect(notification).toHaveBeenNthCalledWith(2, expect.objectContaining({ status: 'info' }))
    })
})
