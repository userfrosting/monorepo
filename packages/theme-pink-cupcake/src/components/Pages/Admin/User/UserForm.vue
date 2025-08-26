<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useUserApi } from '@userfrosting/sprinkle-admin/composables'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import type { UserCreateRequest, UserEditRequest } from '@userfrosting/sprinkle-admin/interfaces'
import type { GroupInterface } from '@userfrosting/sprinkle-account/interfaces'

/**
 * Props - List of groups for the dropdown, plus optional user object for
 * editing.
 */
const props = defineProps<{
    groups: GroupInterface[]
    user?: UserCreateRequest | UserEditRequest
}>()

/**
 * API - Use the user edit API.
 */
const { createUser, updateUser, r$, formData, apiLoading, resetForm } = useUserApi()

/**
 * Helper methods & Variables
 */
const usernameForcedUnlocked = ref<boolean>(false)
const disabledChange = computed<boolean>(() => {
    if (usernameForcedUnlocked.value) return false
    return props.user != null
})

function getAvailableLocales(): string[] {
    return useConfigStore().get('locales.available')
}

function getDefaultLocale(): string {
    return useConfigStore().get('site.registration.user_defaults.locale', 'en_US')
}

/**
 * Watchers - Watch for changes in the group prop and update formData
 * accordingly. Useful when the group prop is updated from the parent component,
 * or the modal is reused.
 */
watch(
    () => props.user,
    (user) => {
        if (user) {
            formData.value.user_name = user.user_name
            formData.value.group_id = user.group_id
            formData.value.first_name = user.first_name
            formData.value.last_name = user.last_name
            formData.value.email = user.email
            formData.value.locale = user.locale
        } else {
            // Apply default locale to form data
            formData.value.locale = getDefaultLocale()
        }
    },
    { immediate: true }
)

/**
 * Emits
 */
const emits = defineEmits(['success'])

/**
 * Methods - Submit the form to the API and handle the response.
 */
const submitForm = async () => {
    // Make sure validation is up to date
    const isValid = await r$.$validate()
    if (!isValid.valid) return

    const apiCall = props.user
        ? updateUser(props.user.user_name, formData.value)
        : createUser(formData.value)
    apiCall
        .then(() => {
            emits('success')
            resetForm()
        })
        .catch(() => {})
}
</script>

<template>
    <form v-on:submit.prevent="submitForm()">
        <fieldset class="uk-fieldset uk-form-stacked">
            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('USERNAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="edit" fixed-width />
                    <button
                        class="uk-button uk-button-default uk-form-button"
                        type="button"
                        :uk-tooltip="$t('USERNAME.UNMODIFIABLE')"
                        @click="usernameForcedUnlocked = !usernameForcedUnlocked"
                        v-if="props.user != null">
                        <font-awesome-icon
                            fixed-width
                            :icon="usernameForcedUnlocked ? 'lock-open' : 'lock'" />
                    </button>
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.user_name.$error }"
                        :disabled="disabledChange"
                        type="text"
                        :placeholder="$t('USERNAME')"
                        aria-label="Username"
                        data-test="user_name"
                        autofocus
                        tabindex="1"
                        autocomplete="false"
                        v-model="formData.user_name" />
                    <UFFormValidationError :errors="r$.$errors.user_name" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('GROUP') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="users" fixed-width />
                    <select
                        class="uk-input uk-select"
                        :class="{ 'uk-form-danger': r$.group_id.$error }"
                        aria-label="Group"
                        data-test="group"
                        tabindex="2"
                        v-model="formData.group_id">
                        <option value="0">None</option>
                        <option disabled="true">-----</option>
                        <option v-for="group in groups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                    <UFFormValidationError :errors="r$.$errors.group_id" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('FIRST_NAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="edit" fixed-width />
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.first_name.$error }"
                        type="text"
                        :placeholder="$t('FIRST_NAME')"
                        aria-label="First Name"
                        data-test="first_name"
                        tabindex="3"
                        v-model="formData.first_name" />
                    <UFFormValidationError :errors="r$.$errors.first_name" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('LAST_NAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="edit" fixed-width />
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.last_name.$error }"
                        type="text"
                        :placeholder="$t('LAST_NAME')"
                        aria-label="Last Name"
                        data-test="last_name"
                        tabindex="4"
                        v-model="formData.last_name" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('EMAIL') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="envelope" fixed-width />
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.email.$error }"
                        type="text"
                        :placeholder="$t('EMAIL')"
                        aria-label="Email"
                        data-test="email"
                        tabindex="5"
                        v-model="formData.email" />
                    <UFFormValidationError :errors="r$.$errors.email" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('LOCALE') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="language" fixed-width />
                    <select
                        class="uk-input uk-select"
                        :class="{ 'uk-form-danger': r$.locale.$error }"
                        aria-label="Locale"
                        data-test="locale"
                        tabindex="6"
                        v-model="formData.locale">
                        <option
                            v-for="(value, key) in getAvailableLocales()"
                            :value="key"
                            :key="key">
                            {{ value }}
                        </option>
                    </select>
                    <UFFormValidationError :errors="r$.$errors.locale" />
                </div>
            </div>

            <div class="uk-text-right" uk-margin>
                <button class="uk-button uk-button-default uk-modal-close" type="button">
                    {{ $t('CANCEL') }}
                </button>
                <button
                    class="uk-button uk-button-primary"
                    :disabled="r$.$error || apiLoading"
                    type="submit"
                    tabindex="7">
                    {{ $t('SAVE') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
