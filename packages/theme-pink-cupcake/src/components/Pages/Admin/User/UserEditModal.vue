<script setup lang="ts">
import UIkit from 'uikit'
import { ref, watch } from 'vue'
import { useGroupsApi, useUserEditApi } from '@userfrosting/sprinkle-admin/composables'
import type { UserEditRequest } from '@userfrosting/sprinkle-admin/interfaces'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import UserForm from './UserForm.vue'

/**
 * Props - The user to edit.
 */
const props = defineProps<{
    user: UserInterface
}>()

/**
 * Variables - Copy the user data to a reactive variable.
 */
const formData = ref<UserEditRequest>({
    user_name: props.user.user_name,
    group_id: props.user.group_id,
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    locale: props.user.locale
})

/**
 * Watchers - Watch for changes in the user prop and update formData
 * accordingly. Useful when the user prop is updated from the parent component,
 * or the modal is reused.
 */
watch(
    () => props.user,
    (newUser: UserInterface) => {
        formData.value = {
            user_name: newUser.user_name,
            group_id: newUser.group_id,
            first_name: newUser.first_name,
            last_name: newUser.last_name,
            email: newUser.email,
            locale: newUser.locale
        }
    },
    { deep: true }
)

/**
 * API - Use the user edit API.
 */
const { submitUserEdit } = useUserEditApi()

/**
 * Emits - Define the saved event. This event is emitted when the form is saved
 * to notify the parent component to refresh the data.
 */
const emits = defineEmits(['saved'])

/**
 * Methods - Submit the form to the API and handle the response.
 */
const submitForm = () => {
    submitUserEdit(props.user.user_name, formData.value)
        .then(() => {
            // Emit the saved event
            emits('saved')

            // Close the modal
            UIkit.modal('#modal-user-edit-' + props.user.user_name).hide()
        })
        .catch(() => {})
}

/**
 * Load group list from API
 */
const { groups, updateGroups } = useGroupsApi()
</script>

<template>
    <a
        href="#"
        v-bind="$attrs"
        :uk-toggle="'target: #modal-user-edit-' + props.user.user_name"
        @click="updateGroups()">
        <slot> <font-awesome-icon icon="pen-to-square" fixed-width /> {{ $t('USER.EDIT') }} </slot>
    </a>

    <!-- This is the modal -->
    <UFModal :id="'modal-user-edit-' + props.user.user_name" closable>
        <template #header>{{ $t('USER.EDIT') }}</template>
        <template #default>
            <UserForm v-model="formData" :groups="groups" @submit="submitForm()" />
        </template>
    </UFModal>
</template>
