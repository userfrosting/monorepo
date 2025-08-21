<script setup lang="ts">
import UIkit from 'uikit'
import GroupForm from './GroupForm.vue'

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
    UIkit.modal('#modal-group-create').hide()
}
</script>

<template>
    <a v-bind="$attrs" :uk-toggle="'target: #modal-group-create'">
        <slot><font-awesome-icon icon="plus" fixed-width /> {{ $t('GROUP.CREATE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModal id="modal-group-create" closable>
        <template #header>{{ $t('GROUP.CREATE') }}</template>
        <template #default>
            <GroupForm @success="formSuccess()" />
        </template>
    </UFModal>
</template>
