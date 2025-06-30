<script setup lang="ts">
import { ref } from 'vue'
import type { LoginRequest } from '@userfrosting/sprinkle-account/interfaces'
import { useLoginApi } from '@userfrosting/sprinkle-account/composables'

/**
 * API - Use the login API.
 */
const { submitLogin, defaultFormData, apiLoading, apiError } = useLoginApi()

/**
 * Variables - Copy the default form data to a reactive variable.
 */
const form = ref<LoginRequest>(defaultFormData())

/**
 * Methods - Submit the form to the API and handle the response.
 */
const sendLogin = async () => {
    await submitLogin(form.value).catch(() => {})
}
</script>

<template>
    <form v-on:submit.prevent="sendLogin()">
        <fieldset class="uk-fieldset">
            <UFAlert data-test="error" v-if="apiError" :alert="apiError" />
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
                    :disabled="apiLoading ? true : false"
                    data-test="submit">
                    {{ $t('LOGIN') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
