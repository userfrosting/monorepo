<script setup lang="ts">
import UIkit from 'uikit'
import { useGroupsApi } from '@userfrosting/sprinkle-admin/composables'
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
const emits = defineEmits(['saved'])

/**
 * Methods - Submit the form to the API and handle the response.
 */
const formSuccess = () => {
    emits('saved')
    UIkit.modal('#modal-user-edit-' + props.user.user_name).hide()
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
            <UserForm :user="props.user" :groups="groups" @success="formSuccess()" />
        </template>
    </UFModal>
</template>
