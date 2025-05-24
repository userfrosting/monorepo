import { defineStore } from 'pinia'
import type { AlertInterface } from '../interfaces'

/**
 * Alerts Store
 *
 * Manages application alerts in a reactive store. Templates can use this
 * store to display alerts to users, including errors, warnings,
 * informational, or success messages. When an alert is added, templates
 * should automatically update the interface to reflect the change.
 */
export const useAlertsStore = defineStore('alerts', {
    state: () => ({
        alerts: [] as AlertInterface[]
    }),
    actions: {
        push(alert: AlertInterface) {
            this.alerts.push(alert)
        },
        pop() {
            return this.alerts.pop()
        },
        shift() {
            return this.alerts.shift()
        },
        clear() {
            this.alerts = []
        }
    }
})
