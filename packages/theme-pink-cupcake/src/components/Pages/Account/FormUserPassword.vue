<script setup lang="ts">
import { ref } from 'vue'
import { useUserPasswordEditApi } from '@userfrosting/sprinkle-account/composables'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import type { PasswordEditRequest } from '@userfrosting/sprinkle-account/interfaces'

/**
 * Variables
 */
const { check } = useAuthStore()
const formData = ref<PasswordEditRequest>({
    passwordcheck: '',
    password: '',
    passwordc: ''
})

/**
 * API - Use the password edit API.
 */
const { submitPasswordEdit } = useUserPasswordEditApi()

/**
 * Methods - Submit the form to the API and handle the response.
 */
const submitForm = () => {
    submitPasswordEdit(formData.value)
        .then(() => {
            // Reset form data
            formData.value = {
                passwordcheck: '',
                password: '',
                passwordc: ''
            }

            // Refresh the user - In case we need to relogin
            check()
        })
        .catch(() => {})
}
</script>

<template>
    <form v-on:submit.prevent="submitForm()">
        <fieldset class="uk-fieldset uk-form-stacked">
            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{
                    $t('PASSWORD.NEW')
                }}</label>
                <!-- <span class="uk-text-meta">{{ $t('PASSWORD.CONFIRM_NEW_EXPLAIN') }}</span> -->
                <!-- {{translate('PASSWORD.BETWEEN', {min: site.password.length.min, max: site.password.length.max})}} -->
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="key" fixed-width />
                    <input
                        class="uk-input"
                        type="password"
                        :placeholder="$t('PASSWORD.NEW')"
                        aria-label="Password"
                        data-test="password"
                        autofocus
                        tabindex="1"
                        autocomplete="false"
                        v-model="formData.password" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{
                    $t('PASSWORD.CONFIRM_NEW')
                }}</label>
                <span class="uk-text-meta">{{ $t('PASSWORD.CONFIRM_NEW_EXPLAIN') }}</span>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="key" fixed-width />
                    <input
                        class="uk-input"
                        type="password"
                        :placeholder="$t('PASSWORD.CONFIRM_NEW')"
                        aria-label="Confirm Password"
                        data-test="passwordc"
                        tabindex="2"
                        v-model="formData.passwordc" />
                </div>
            </div>

            <hr />

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{
                    $t('PASSWORD.CURRENT')
                }}</label>
                <span class="uk-text-meta">{{ $t('PASSWORD.CURRENT_EXPLAIN') }}</span>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="key" fixed-width />
                    <input
                        class="uk-input"
                        type="password"
                        :placeholder="$t('PASSWORD.CURRENT')"
                        aria-label="Current Password"
                        data-test="passwordcheck"
                        tabindex="3"
                        v-model="formData.passwordcheck" />
                </div>
            </div>

            <div class="uk-text-center" uk-margin>
                <button class="uk-button uk-button-primary" type="submit" tabindex="4">
                    {{ $t('SAVE') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
