import { defineStore } from 'pinia'
import axios from 'axios'
import type {
    UserInterface,
    LoginRequest,
    LoginResponse,
    AuthCheckResponse,
    UserPermissionsMapInterface
} from '../interfaces'
import { type AlertInterface, Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'

export const useAuthStore = defineStore('auth', {
    persist: true,
    state: () => {
        return {
            user: null as UserInterface | null,
            permissions: null as UserPermissionsMapInterface | null
        }
    },
    getters: {
        isAuthenticated: (state): boolean => state.user !== null
    },
    actions: {
        setUser(user: UserInterface, permissions: UserPermissionsMapInterface): void {
            this.user = user
            this.permissions = permissions
        },
        unsetUser(): void {
            this.user = null
            this.permissions = null
        },
        async login(form: LoginRequest) {
            return axios
                .post<LoginResponse>('/account/login', form)
                .then((response) => {
                    this.setUser(response.data.user, response.data.permissions)

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
                .get<AuthCheckResponse>('/account/auth-check')
                .then((response) => {
                    if (response.data.user === null) {
                        this.unsetUser()
                    } else {
                        this.setUser(response.data.user, response.data.permissions ?? {})
                    }

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
