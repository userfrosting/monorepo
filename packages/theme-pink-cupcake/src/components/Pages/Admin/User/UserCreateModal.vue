<script setup lang="ts">
import UIkit from 'uikit'
import { useGroupsApi } from '@userfrosting/sprinkle-admin/composables'
import UserForm from './UserForm.vue'

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
    UIkit.modal('#modal-user-create').hide()
}

/**
 * Load group list from API
 */
const { groups, updateGroups } = useGroupsApi()
</script>

<template>
    <a v-bind="$attrs" :uk-toggle="'target: #modal-user-create'" @click="updateGroups()">
        <slot><font-awesome-icon icon="user-plus" /> {{ $t('USER.CREATE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModal id="modal-user-create" closable>
        <template #header>{{ $t('USER.CREATE') }}</template>
        <template #default>
            <UserForm :groups="groups" @success="formSuccess()" />
        </template>
    </UFModal>
</template>
