import { defineStore } from 'pinia'
import axios from 'axios'
import type { UserInterface, LoginRequest, LoginResponse } from '../interfaces'
import { type AlertInterface, Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'

interface AuthCheckApi {
    auth: boolean
    user: UserInterface
}

export const useAuthStore = defineStore('auth', {
    persist: true,
    state: () => {
        return {
            user: null as UserInterface | null
        }
    },
    getters: {
        isAuthenticated: (state): boolean => state.user !== null
    },
    actions: {
        setUser(user: UserInterface): void {
            this.user = user
        },
        unsetUser(): void {
            this.user = null
        },
        async login(form: LoginRequest) {
            return axios
                .post<LoginResponse>('/account/login', form)
                .then((response) => {
                    this.setUser(response.data.user)

                    // Reload the translator dictionary to reflect the user's language
                    useTranslator().load()

                    return response.data
                })
                .catch((err) => {
                    const error: AlertInterface = {
                        ...{
                            description: 'An error as occurred',
                            style: Severity.Danger,
                            closeBtn: true
                        },
                        ...err.response.data
                    }

                    throw error
                })
        },
        async check() {
            return axios
                .get<AuthCheckApi>('/account/auth-check')
                .then((response) => {
                    this.setUser(response.data.user)

                    return this.user
                })
                .catch((err) => {
                    this.unsetUser()

                    const error: AlertInterface = {
                        ...{
                            description: 'An error as occurred',
                            style: Severity.Danger,
                            closeBtn: true
                        },
                        ...err.response.data
                    }

                    throw error
                })
        },
        async logout() {
            this.unsetUser()
            return axios
                .get('/account/logout')
                .then(() => {
                    // Reload the translator dictionary to reflect the default language
                    useTranslator().load()
                })
                .catch((err) => {
                    const error: AlertInterface = {
                        ...{
                            description: 'An error as occurred',
                            style: Severity.Danger,
                            closeBtn: true
                        },
                        ...err.response.data
                    }

                    throw error
                })
        }
    }
})
