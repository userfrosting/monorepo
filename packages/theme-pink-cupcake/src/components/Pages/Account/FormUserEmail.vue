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

const formData = ref<EmailEditRequest>({
    email: user.email,
    passwordcheck: ''
})

/**
 * API - Use the password edit API.
 */
const { submitEmailEdit } = useUserEmailEditApi()

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
                        type="string"
                        placeholder="New Email"
                        aria-label="email"
                        data-test="email"
                        autofocus
                        tabindex="1"
                        autocomplete="false"
                        v-model="formData.email" />
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
                        type="password"
                        :placeholder="$t('PASSWORD.CURRENT')"
                        aria-label="Current Password"
                        data-test="passwordcheck"
                        tabindex="2"
                        v-model="formData.passwordcheck" />
                </div>
            </div>

            <div class="uk-text-center" uk-margin>
                <button class="uk-button uk-button-primary" type="submit" tabindex="3">
                    {{ $t('SAVE') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
