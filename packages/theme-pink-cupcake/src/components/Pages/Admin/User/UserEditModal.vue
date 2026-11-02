<script setup lang="ts">
import UIkit from 'uikit'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import UserForm from './UserForm.vue'

/**
 * Props - The user to edit.
 */
const props = defineProps<{
    user: UserInterface
}>()

/**
 * Emits - Define the saved event. This event is emitted when the form is saved
 * to notify the parent component to refresh the data.
 */
const emits = defineEmits<{
    saved: [user_name?: string]
}>()

/**
 * Methods - Submit the form to the API and handle the response.
 */
const formSuccess = (user_name?: string) => {
    emits('saved', user_name)
    UIkit.modal('#modal-user-edit-' + props.user.id).hide()
}
</script>

<template>
    <a :href="'#modal-user-edit-' + props.user.id" v-bind="$attrs" uk-toggle>
        <slot> <font-awesome-icon icon="pen-to-square" fixed-width /> {{ $t('USER.EDIT') }} </slot>
    </a>

    <!-- This is the modal -->
    <UFModal :id="'modal-user-edit-' + props.user.id" closable>
        <template #header>{{ $t('USER.EDIT') }}</template>
        <template #default>
            <UserForm :user="props.user" @success="formSuccess($event)" />
        </template>
    </UFModal>
</template>
