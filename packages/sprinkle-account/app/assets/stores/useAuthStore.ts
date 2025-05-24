import { defineStore } from 'pinia'
import axios from 'axios'
import type {
    LoginRequest,
    LoginResponse,
    AuthCheckResponse,
    UserDataInterface
} from '../interfaces'
import { type AlertInterface, Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useCsrf } from '@userfrosting/sprinkle-core/composables'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'
import { useAuthorizationManager } from '../composables/useAuthorizationManager'

export const useAuthStore = defineStore('auth', {
    persist: true,
    state: () => {
        return {
            user: null as UserDataInterface | null
        }
    },
    getters: {
        isAuthenticated: (state): boolean => state.user !== null,
        checkAccess:
            (state) =>
            (slug: string): Boolean => {
                const authorizer = useAuthorizationManager(state.user)
                return authorizer.checkAccess(slug)
            }
    },
    actions: {
        setUser(user: UserDataInterface): void {
            this.user = user
        },
        unsetUser(): void {
            this.user = null

            // TODO : The locale and CSRF should probably be reset to the default values here
        },
        async login(form: LoginRequest) {
            return axios
                .post<LoginResponse>('/account/login', form)
                .then((response) => {
                    this.setUser(response.data.user)

                    // Reload the translator dictionary to reflect the user's language
                    useTranslator().load()

                    // Update the CSRF token
                    useCsrf().updateFromHeaders(response.headers)

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
                    this.setUser(response.data)

                    return this.user
                })
                .catch((err) => {
                    // Test status is 401 and unset user, otherwise, throw error
                    if (err.response.status === 401) {
                        this.unsetUser()
                        // TODO : See above. The locale and CSRF should won't be reset to the default values here
                    } else {
                        const error: AlertInterface = {
                            ...{
                                description: 'An error as occurred',
                                style: Severity.Danger,
                                closeBtn: true
                            },
                            ...err.response.data
                        }

                        throw error
                    }
                })
        },
        async logout() {
            this.unsetUser()
            return axios
                .get('/account/logout')
                .then((response) => {
                    // Reload the translator dictionary to reflect the default language
                    useTranslator().load()

                    // Update the CSRF token
                    useCsrf().updateFromHeaders(response.headers)
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
