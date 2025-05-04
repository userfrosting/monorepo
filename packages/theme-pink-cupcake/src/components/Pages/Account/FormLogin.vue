<script setup lang="ts">
import { ref } from 'vue'
import UIkit from 'uikit'
import type { AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
import type { LoginRequest } from '@userfrosting/sprinkle-account/interfaces'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'

// Variables
const loading = ref(false)
const error = ref<AlertInterface | null>()
let form: LoginRequest = {
    user_name: '',
    password: '',
    rememberme: false
}

// Form action
async function sendLogin() {
    loading.value = true
    error.value = null
    const auth = useAuthStore()
    await auth
        .login(form)
        .then((response) => {
            UIkit.notification({
                message: response.message,
                status: 'primary',
                pos: 'top-right',
                timeout: 4000
            })
        })
        .catch((err: AlertInterface) => {
            error.value = err
        })
        .finally(() => {
            loading.value = false
        })
}
</script>

<template>
    <form v-on:submit.prevent="sendLogin()">
        <fieldset class="uk-fieldset">
            <UFAlert data-test="error" v-if="error" :alert="error" />
            <div class="uk-margin">
                <label class="uk-form-label" for="first_name">{{ $t('USERNAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <span class="uk-form-icon">
                        <font-awesome-icon :icon="['fas', 'user']" />
                    </span>
                    <input
                        class="uk-input"
                        type="text"
                        :placeholder="$t('USERNAME')"
                        aria-label="Username"
                        data-test="username"
                        v-model="form.user_name" />
                </div>
            </div>
            <div class="uk-margin">
                <label class="uk-form-label" for="first_name">{{ $t('PASSWORD') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <span class="uk-form-icon">
                        <font-awesome-icon :icon="['fas', 'lock']" />
                    </span>
                    <input
                        class="uk-input"
                        type="password"
                        :placeholder="$t('PASSWORD')"
                        aria-label="Password"
                        data-test="password"
                        v-model="form.password" />
                </div>
            </div>
            <div class="uk-margin">
                <label class="uk-inline uk-width-1-1">
                    <input
                        class="uk-checkbox"
                        type="checkbox"
                        aria-label="Remember Me"
                        data-test="rememberme"
                        v-model="form.rememberme" />
                    {{ $t('REMEMBER_ME') }}
                </label>
            </div>
            <div class="uk-text-center">
                <button
                    class="uk-button uk-button-primary uk-width-1-3"
                    :disabled="loading"
                    data-test="submit">
                    {{ $t('LOGIN') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
