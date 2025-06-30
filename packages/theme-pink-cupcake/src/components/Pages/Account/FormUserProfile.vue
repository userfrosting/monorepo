<script setup lang="ts">
import { ref } from 'vue'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import { useUserProfileEditApi } from '@userfrosting/sprinkle-account/composables'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import type { ProfileEditRequest } from '@userfrosting/sprinkle-account/interfaces'

/**
 * Variables - Copy the user data to a reactive variable.
 */
const { user, check } = useAuthStore()

// User should be logged in, so not null
if (user === null) {
    throw new Error('User is null.')
}

const formData = ref<ProfileEditRequest>({
    first_name: user.first_name,
    last_name: user.last_name,
    locale: user.locale
})

function getAvailableLocales(): string[] {
    return useConfigStore().get('locales.available')
}

/**
 * API - Use the profile edit API.
 */
const { submitProfileEdit } = useUserProfileEditApi()

/**
 * Methods - Submit the form to the API and handle the response.
 */
const submitForm = () => {
    submitProfileEdit(formData.value).then(check)
}
</script>

<template>
    <form v-on:submit.prevent="submitForm()">
        <fieldset class="uk-fieldset uk-form-stacked">
            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('FIRST_NAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="edit" fixed-width />
                    <input
                        class="uk-input"
                        type="text"
                        :placeholder="$t('FIRST_NAME')"
                        aria-label="First Name"
                        data-test="first_name"
                        tabindex="1"
                        autofocus
                        v-model="formData.first_name" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('LAST_NAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="edit" fixed-width />
                    <input
                        class="uk-input"
                        type="text"
                        :placeholder="$t('LAST_NAME')"
                        aria-label="Last Name"
                        data-test="last_name"
                        tabindex="2"
                        v-model="formData.last_name" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('LOCALE') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="language" fixed-width />
                    <select
                        class="uk-input uk-select"
                        aria-label="Locale"
                        data-test="locale"
                        tabindex="3"
                        v-model="formData.locale">
                        <option
                            v-for="(value, key) in getAvailableLocales()"
                            :value="key"
                            :key="key">
                            {{ value }}
                        </option>
                    </select>
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
