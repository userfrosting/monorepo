<script setup lang="ts">
import UIkit from 'uikit'
import RoleForm from './RoleForm.vue'

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
    UIkit.modal('#modal-role-create').hide()
}
</script>

<template>
    <a v-bind="$attrs" :uk-toggle="'target: #modal-role-create'">
        <slot><font-awesome-icon icon="plus" /> {{ $t('ROLE.CREATE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModal id="modal-role-create" closable>
        <template #header>{{ $t('ROLE.CREATE') }}</template>
        <template #default>
            <RoleForm @success="formSuccess()" />
        </template>
    </UFModal>
</template>
