import { defineStore } from 'pinia'
import axios from 'axios'
import type { DashboardResponse } from '../interfaces'

const defaultDashboardApi: DashboardResponse = {
    counter: {
        users: 0,
        roles: 0,
        groups: 0
    },
    users: []
}

export const useDashboardApi = defineStore('dashboardApi', {
    state: () => {
        return {
            data: defaultDashboardApi
        }
    },
    actions: {
        async load() {
            return axios
                .get<DashboardResponse>('/api/dashboard')
                .then((response) => {
                    this.data = response.data

                    return this.data
                })
                .catch(() => {})
        }
    }
})
