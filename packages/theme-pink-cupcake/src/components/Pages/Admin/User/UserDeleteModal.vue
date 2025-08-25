<script setup lang="ts">
import { useUserApi } from '@userfrosting/sprinkle-admin/composables'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

/**
 * Variables and composables
 */
const { deleteUser } = useUserApi()

/**
 * Props - The user object to delete
 */
const props = defineProps<{
    user: UserInterface
}>()

/**
 * Emits - Define the deleted event. This event is emitted when the user is deleted
 * to notify the parent component to refresh the data.
 */
const emits = defineEmits(['deleted'])

/**
 * Methods - Submit the form to the API and handle the response.
 */
const deleteConfirmed = () => {
    deleteUser(props.user.user_name)
        .then(() => {
            emits('deleted')
        })
        .catch(() => {})
}
</script>

<template>
    <a href="#" v-bind="$attrs" :uk-toggle="'target: #confirm-user-delete-' + props.user.user_name">
        <slot><font-awesome-icon icon="trash" fixed-width /> {{ $t('USER.DELETE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModalConfirmation
        :id="'confirm-user-delete-' + props.user.user_name"
        title="USER.DELETE"
        @confirmed="deleteConfirmed()"
        acceptLabel="USER.DELETE"
        acceptIcon="trash"
        :rejectIcon="null"
        :acceptSeverity="Severity.Danger">
        <template #prompt>
            <div v-html="$t('USER.DELETE_CONFIRM', props.user)"></div>
        </template>
    </UFModalConfirmation>
</template>
