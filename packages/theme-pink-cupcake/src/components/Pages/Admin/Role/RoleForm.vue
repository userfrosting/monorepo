<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { RoleInterface } from '@userfrosting/sprinkle-account/interfaces'
import { useRoleApi } from '@userfrosting/sprinkle-admin/composables'

/**
 * Props - Optional role object for editing.
 */
const props = defineProps<{ role?: RoleInterface }>()

/**
 * API - Use the role edit API.
 */
const { createRole, updateRole, r$, formData, apiLoading, resetForm } = useRoleApi()

/**
 * Helper methods & Variables
 */
const slugForcedUnlocked = ref<boolean>(false)
const disabledChange = computed<boolean>(() => {
    if (slugForcedUnlocked.value) return false
    return props.role != null
})

/**
 * Watchers - Watch for changes in the role prop and update formData
 * accordingly. Useful when the role prop is updated from the parent component,
 * or the modal is reused.
 */
watch(
    () => props.role,
    (role) => {
        if (role) {
            formData.value.slug = role.slug
            formData.value.name = role.name
            formData.value.description = role.description
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

    const apiCall = props.role
        ? updateRole(props.role.slug, formData.value)
        : createRole(formData.value)
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
                <label class="uk-form-label" for="form-stacked-text">{{ $t('ROLE.NAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="pen-to-square" fixed-width />
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.name.$error }"
                        type="text"
                        :placeholder="$t('ROLE.NAME_EXPLAIN')"
                        aria-label="Role Name"
                        data-test="name"
                        autofocus
                        tabindex="1"
                        v-model="formData.name" />
                    <UFFormValidationError :errors="r$.$errors.name" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('SLUG') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="tag" fixed-width />
                    <button
                        class="uk-button uk-button-default uk-form-button"
                        type="button"
                        :uk-tooltip="$t('ROLE.SLUG_UNMODIFIABLE')"
                        @click="slugForcedUnlocked = !slugForcedUnlocked"
                        v-if="props.role != null">
                        <font-awesome-icon
                            fixed-width
                            :icon="slugForcedUnlocked ? 'lock-open' : 'lock'" />
                    </button>
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.slug.$error }"
                        :disabled="disabledChange"
                        type="text"
                        :placeholder="$t('SLUG')"
                        aria-label="Role Slug"
                        data-test="slug"
                        tabindex="2"
                        v-model="formData.slug" />
                    <UFFormValidationError :errors="r$.$errors.slug" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('DESCRIPTION') }}</label>
                <textarea
                    class="uk-textarea"
                    :class="{ 'uk-form-danger': r$.description.$error }"
                    :placeholder="$t('DESCRIPTION')"
                    aria-label="Description"
                    data-test="description"
                    rows="6"
                    tabindex="3"
                    v-model="formData.description" />
                <UFFormValidationError :errors="r$.$errors.description" />
            </div>

            <div class="uk-text-right" uk-margin>
                <button class="uk-button uk-button-default uk-modal-close" type="button">
                    {{ $t('CANCEL') }}
                </button>
                <button
                    class="uk-button uk-button-primary"
                    :disabled="r$.$error || apiLoading"
                    type="submit"
                    tabindex="4">
                    {{ $t('SAVE') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
