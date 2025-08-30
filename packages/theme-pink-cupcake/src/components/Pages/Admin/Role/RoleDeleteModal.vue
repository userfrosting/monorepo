<script setup lang="ts">
import { useRoleApi } from '@userfrosting/sprinkle-admin/composables'
import type { RoleInterface } from '@userfrosting/sprinkle-account/interfaces'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

/**
 * Variables and composables
 */
const { deleteRole } = useRoleApi()

/**
 * Props - The group object to delete
 */
const props = defineProps<{
    role: RoleInterface
}>()

/**
 * Emits - Define the deleted event. This event is emitted when the group is deleted
 * to notify the parent component to refresh the data.
 */
const emits = defineEmits(['deleted'])

/**
 * Methods - Submit the form to the API and handle the response.
 */
const deleteConfirmed = () => {
    deleteRole(props.role.slug)
        .then(() => {
            emits('deleted')
        })
        .catch(() => {})
}
</script>

<template>
    <a :href="'#confirm-role-delete-' + props.role.id" v-bind="$attrs" uk-toggle>
        <slot><font-awesome-icon icon="trash" fixed-width /> {{ $t('ROLE.DELETE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModalConfirmation
        :id="'confirm-role-delete-' + props.role.id"
        :title="$t('ROLE.DELETE')"
        @confirmed="deleteConfirmed()"
        :acceptLabel="$t('ROLE.DELETE_YES')"
        acceptIcon="trash"
        :rejectIcon="null"
        :acceptSeverity="Severity.Danger">
        <template #prompt>
            <div v-html="$t('ROLE.DELETE_CONFIRM', role)"></div>
        </template>
    </UFModalConfirmation>
</template>
