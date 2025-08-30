<script setup lang="ts">
import UIkit from 'uikit'
import type { RoleInterface } from '@userfrosting/sprinkle-account/interfaces'
import RoleForm from './RoleForm.vue'

/**
 * Props - The role to edit.
 */
const props = defineProps<{
    role: RoleInterface
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
    UIkit.modal('#modal-role-edit-' + props.role.id).hide()
}
</script>

<template>
    <a :href="'#modal-role-edit-' + props.role.id" v-bind="$attrs" uk-toggle>
        <slot> <font-awesome-icon icon="pen-to-square" fixed-width /> {{ $t('ROLE.EDIT') }} </slot>
    </a>

    <!-- This is the modal -->
    <UFModal :id="'modal-role-edit-' + props.role.id" closable>
        <template #header> {{ $t('ROLE.EDIT') }} </template>
        <template #default>
            <RoleForm :role="props.role" @success="formSuccess()" />
        </template>
    </UFModal>
</template>
