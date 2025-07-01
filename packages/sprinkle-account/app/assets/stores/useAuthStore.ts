import { defineStore } from 'pinia'
import axios from 'axios'
import type { AuthCheckResponse, UserDataInterface } from '../interfaces'
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

            // Reload the translator dictionary to reflect the user's language
            useTranslator().load()
        },
        unsetUser(): void {
            this.user = null

            // Reload the translator dictionary to reflect the default language
            useTranslator().load()
        },
        async check() {
            return axios
                .get<AuthCheckResponse>('/account/auth')
                .then((response) => {
                    this.setUser(response.data)

                    return this.user
                })
                .catch(() => {})
        }
    }
})
