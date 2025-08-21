<script setup lang="ts">
import UIkit from 'uikit'
import type { GroupInterface } from '@userfrosting/sprinkle-account/interfaces'
import GroupForm from './GroupForm.vue'

/**
 * Props - The group to edit.
 */
const props = defineProps<{
    group: GroupInterface
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
    UIkit.modal('#modal-group-edit-' + props.group.slug).hide()
}
</script>

<template>
    <a href="#" v-bind="$attrs" :uk-toggle="'target: #modal-group-edit-' + props.group.slug">
        <slot> <font-awesome-icon icon="pen-to-square" fixed-width /> {{ $t('GROUP.EDIT') }} </slot>
    </a>

    <!-- This is the modal -->
    <UFModal :id="'modal-group-edit-' + props.group.slug" closable>
        <template #header> {{ $t('GROUP.EDIT') }} </template>
        <template #default>
            <GroupForm :group="props.group" @success="formSuccess()" />
        </template>
    </UFModal>
</template>
