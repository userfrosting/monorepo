<script setup lang="ts">
import UIkit from 'uikit'
import { ref, watch } from 'vue'
import { useUserUpdateApi } from '@userfrosting/sprinkle-admin/composables'
import type { UserPasswordRequest } from '@userfrosting/sprinkle-admin/interfaces'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import UserPasswordForm from './UserPasswordForm.vue'

/**
 * Props - The user to edit.
 */
const props = defineProps<{
    user: UserInterface
}>()

/**
 * Variables - Copy the user data to a reactive variable.
 */
const formData = ref<UserPasswordRequest>({
    password: '',
    passwordc: ''
})

/**
 * Watchers - Watch for changes in the user prop and update formData
 * accordingly. Reset the form data when the modal is reused.
 */
watch(
    () => props.user,
    () => {
        formData.value = {
            password: '',
            passwordc: ''
        }
    },
    { deep: true }
)

/**
 * API - Use the user edit API.
 */
const { submitUserUpdate } = useUserUpdateApi()

/**
 * Methods - Submit the form to the API and handle the response.
 */
const submitForm = () => {
    submitUserUpdate(props.user.user_name, 'password', formData.value).then(() => {
        // Close the modal
        UIkit.modal('#modal-user-password-' + props.user.id).hide()
    })
}
</script>

<template>
    <a :href="'#modal-user-password-' + props.user.id" v-bind="$attrs" uk-toggle>
        <slot>
            <font-awesome-icon icon="key" fixed-width /> {{ $t('USER.ADMIN.CHANGE_PASSWORD') }}
        </slot>
    </a>

    <!-- This is the modal -->
    <UFModal :id="'modal-user-password-' + props.user.id" closable>
        <template #header> {{ $t('USER.ADMIN.CHANGE_PASSWORD') }} </template>
        <template #default>
            <UserPasswordForm v-model="formData" @submit="submitForm()" />
        </template>
    </UFModal>
</template>
