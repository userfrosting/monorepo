<script setup lang="ts">
import { ref } from 'vue'
import { useUserEmailEditApi } from '@userfrosting/sprinkle-account/composables'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import type { EmailEditRequest } from '@userfrosting/sprinkle-account/interfaces'

/**
 * Variables - Copy the user data to a reactive variable.
 */
const { user, check } = useAuthStore()

// User should be logged in, so not null
if (user === null) {
    throw new Error('User is null.')
}

/**
 * API - Use the password edit API.
 */
const { submitEmailEdit, r$, formData, apiLoading } = useUserEmailEditApi()

/**
 * Initialize form data with user profile information.
 */
formData.value = {
    email: user.email,
    passwordcheck: ''
}

/**
 * Methods - Submit the form to the API and handle the response.
 */
const submitForm = () => {
    submitEmailEdit(formData.value).then(() => {
        // Reset form data
        formData.value.passwordcheck = ''

        // Refresh the user
        check()
    })
}
</script>

<template>
    <form v-on:submit.prevent="submitForm()">
        <fieldset class="uk-fieldset uk-form-stacked">
            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('EMAIL') }}</label>
                <span class="uk-text-meta">{{ $t('EMAIL.YOUR') }}</span>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="envelope" fixed-width />
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.email.$error }"
                        type="string"
                        placeholder="New Email"
                        aria-label="email"
                        data-test="email"
                        autofocus
                        tabindex="1"
                        autocomplete="off"
                        data-1p-ignore data-lpignore="true" data-protonpass-ignore="true"
                        v-model="formData.email" />
                    <UFFormValidationError :errors="r$.$errors.email" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{
                    $t('PASSWORD.CURRENT')
                }}</label>
                <span class="uk-text-meta">{{ $t('PASSWORD.CURRENT_EXPLAIN') }}</span>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="key" fixed-width />
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.passwordcheck.$error }"
                        type="password"
                        :placeholder="$t('PASSWORD.CURRENT')"
                        aria-label="Current Password"
                        data-test="passwordcheck"
                        tabindex="2"
                        v-model="formData.passwordcheck" />
                    <UFFormValidationError :errors="r$.$errors.passwordcheck" />
                </div>
            </div>

            <div class="uk-text-center" uk-margin>
                <button
                    class="uk-button uk-button-primary"
                    :disabled="r$.$error || apiLoading"
                    type="submit"
                    tabindex="3">
                    {{ $t('SAVE') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
