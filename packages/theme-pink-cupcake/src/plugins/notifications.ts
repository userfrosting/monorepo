import { watch } from 'vue'
import UIkit from 'uikit'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { storeToRefs } from 'pinia'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core/interfaces'

/**
 * UiKit Notifications plugin.
 *
 * This plugin watch the alerts store and displays notifications using UIkit.
 * It shows the last alert in the top-right corner of the screen by default.
 */
export default {
    install: () => {
        const alertsStore = useAlertsStore()
        const { alerts } = storeToRefs(alertsStore)
        watch(
            alerts,
            (alerts: AlertInterface[]) => {
                while (alerts.length) {
                    const alert = alertsStore.shift()
                    if (!alert) break
                    UIkit.notification({
                        message: getAlertMessage(alert),
                        status: severityToStatus((alert.style ?? Severity.Default) as Severity),
                        pos: 'top-right',
                        timeout: 4000
                    })
                }
            },
            { deep: true }
        )
    }
}

/**
 * Concatenates the title and description of an alert, if they are set.
 *
 * @param alert The alert object.
 * @returns The concatenated string.
 */
function getAlertMessage(alert: AlertInterface): string {
    if (alert.title && alert.description)
        return `<strong>${alert.title}</strong>: ${alert.description}`
    return alert.title || alert.description || ''
}

/**
 * Translates an alert severity to a UIkit notification status.
 *
 * @param severity The severity level of the alert.
 * @returns The corresponding UIkit notification status.
 */
function severityToStatus(
    severity: Severity
): 'success' | 'warning' | 'danger' | 'primary' | 'info' | undefined {
    switch (severity) {
        case Severity.Success:
            return 'success'
        case Severity.Warning:
            return 'warning'
        case Severity.Danger:
            return 'danger'
        case Severity.Info:
            return 'info'
        case Severity.Secondary:
        case Severity.Muted:
            return undefined
        default:
            return 'primary'
    }
}
