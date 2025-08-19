<script setup lang="ts">
import { useUserPasswordEditApi } from '@userfrosting/sprinkle-account/composables'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'

/**
 * Variables
 */
const { check } = useAuthStore()

/**
 * API - Use the password edit API.
 */
const { submitPasswordEdit, r$, formData, apiLoading, minLength, maxLength } =
    useUserPasswordEditApi()

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
                <span class="uk-text-meta">
                    {{ $t('PASSWORD.BETWEEN', { min: minLength, max: maxLength }) }}
                </span>
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
                    <UFFormValidationError :errors="r$.$errors.password" />
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
                    <UFFormValidationError :errors="r$.$errors.passwordc" />
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
                    <UFFormValidationError :errors="r$.$errors.passwordcheck" />
                </div>
            </div>

            <div class="uk-text-center" uk-margin>
                <button
                    class="uk-button uk-button-primary"
                    type="submit"
                    tabindex="4"
                    :disabled="r$.$error || apiLoading">
                    {{ $t('SAVE') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
