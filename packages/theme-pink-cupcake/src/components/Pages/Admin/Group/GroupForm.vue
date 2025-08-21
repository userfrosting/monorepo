<script setup lang="ts">
import { watch } from 'vue'
import { useGroupApi } from '@userfrosting/sprinkle-admin/composables'
import type { GroupInterface } from '@userfrosting/sprinkle-account/interfaces'

/**
 * Props - Optional group object for editing.
 */
const props = defineProps<{ group?: GroupInterface }>()

/**
 * API - Use the group edit API.
 */
const { createGroup, updateGroup, r$, formData, apiLoading, resetForm } = useGroupApi()

/**
 * Watchers - Watch for changes in the group prop and update formData
 * accordingly. Useful when the group prop is updated from the parent component,
 * or the modal is reused.
 */
watch(
    () => props.group,
    (group) => {
        if (group) {
            formData.value.slug = group.slug
            formData.value.name = group.name
            formData.value.description = group.description
            formData.value.icon = group.icon
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
const submitForm = () => {
    const apiCall = props.group
        ? updateGroup(props.group.slug, formData.value)
        : createGroup(formData.value)
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
                <label class="uk-form-label" for="form-stacked-text">{{ $t('GROUP.NAME') }}</label>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon class="fa-form-icon" icon="pen-to-square" fixed-width />
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.name.$error }"
                        type="text"
                        :placeholder="$t('GROUP.NAME_EXPLAIN')"
                        aria-label="Group Name"
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
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.slug.$error }"
                        type="text"
                        :placeholder="$t('SLUG')"
                        aria-label="Group Slug"
                        data-test="slug"
                        tabindex="2"
                        v-model="formData.slug" />
                    <UFFormValidationError :errors="r$.$errors.slug" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('GROUP.ICON') }}</label>
                <span class="uk-text-meta">{{ $t('GROUP.ICON_EXPLAIN') }}</span>
                <div class="uk-inline uk-width-1-1">
                    <font-awesome-icon
                        class="fa-form-icon"
                        :icon="formData.icon"
                        v-if="formData.icon"
                        fixed-width />
                    <input
                        class="uk-input"
                        :class="{ 'uk-form-danger': r$.icon.$error }"
                        type="text"
                        :placeholder="$t('GROUP.ICON')"
                        aria-label="Group Icon"
                        data-test="icon"
                        tabindex="3"
                        v-model="formData.icon" />
                    <UFFormValidationError :errors="r$.$errors.icon" />
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{ $t('DESCRIPTION') }}</label>
                <textarea
                    class="uk-textarea"
                    :class="{ 'uk-form-danger': r$.description.$error }"
                    placeholder="Group Description"
                    aria-label="Description"
                    data-test="description"
                    rows="6"
                    tabindex="4"
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
                    tabindex="5">
                    {{ $t('SAVE') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
