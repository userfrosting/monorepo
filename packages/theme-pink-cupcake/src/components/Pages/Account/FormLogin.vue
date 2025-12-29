<script setup lang="ts">
import { useLoginApi } from '@userfrosting/sprinkle-account/composables'

/**
 * API - Use the login API.
 */
const { submitLogin, apiLoading, apiError, formData, r$ } = useLoginApi()

/**
 * Methods - Submit the form to the API and handle the response.
 */
const sendLogin = async () => {
    // Make sure validation is up to date
    const isValid = await r$.$validate()
    if (!isValid.valid) return

    await submitLogin(formData.value).catch(() => {})
}
</script>

<template>
    <form v-on:submit.prevent="sendLogin()">
        <fieldset class="uk-fieldset">
            <UFAlert data-test="error" v-if="apiError" :alert="apiError" />
            <div class="uk-margin">
                <label class="uk-form-label" for="first_name">{{ $t('USERNAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="user" fixed-width />
                    <input
                        class="uk-input"
                        type="text"
                        :placeholder="$t('USERNAME')"
                        aria-label="Username"
                        data-test="username"
                        v-model="formData.user_name" />
                    <UFFormValidationError :errors="r$.$errors.user_name" />
                </div>
            </div>
            <div class="uk-margin">
                <label class="uk-form-label" for="first_name">{{ $t('PASSWORD') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="lock" fixed-width />
                    <input
                        class="uk-input"
                        type="password"
                        :placeholder="$t('PASSWORD')"
                        aria-label="Password"
                        data-test="password"
                        v-model="formData.password" />
                    <UFFormValidationError :errors="r$.$errors.password" />
                </div>
            </div>
            <div class="uk-margin">
                <label class="uk-inline uk-width-1-1">
                    <input
                        class="uk-checkbox"
                        type="checkbox"
                        aria-label="Remember Me"
                        data-test="rememberme"
                        v-model="formData.rememberme" />
                    {{ $t('REMEMBER_ME') }}
                </label>
            </div>
            <div class="uk-text-center">
                <button
                    class="uk-button uk-button-primary uk-width-1-3@s"
                    :disabled="apiLoading ? true : false"
                    data-test="submit">
                    {{ $t('LOGIN') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
