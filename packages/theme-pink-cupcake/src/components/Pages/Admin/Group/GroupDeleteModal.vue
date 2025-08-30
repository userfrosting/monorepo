<script setup lang="ts">
import { useGroupApi } from '@userfrosting/sprinkle-admin/composables'
import type { GroupInterface } from '@userfrosting/sprinkle-account/interfaces'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

/**
 * Variables and composables
 */
const { deleteGroup } = useGroupApi()

/**
 * Props - The group object to delete
 */
const props = defineProps<{
    group: GroupInterface
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
    deleteGroup(props.group.slug)
        .then(() => {
            emits('deleted')
        })
        .catch(() => {})
}
</script>

<template>
    <a :href="'#confirm-group-delete-' + props.group.id" v-bind="$attrs" uk-toggle>
        <slot><font-awesome-icon icon="trash" fixed-width /> {{ $t('GROUP.DELETE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModalConfirmation
        :id="'confirm-group-delete-' + props.group.id"
        title="GROUP.DELETE"
        @confirmed="deleteConfirmed()"
        acceptLabel="GROUP.DELETE_YES"
        acceptIcon="trash"
        :rejectIcon="null"
        :acceptSeverity="Severity.Danger">
        <template #prompt>
            <div v-html="$t('GROUP.DELETE_CONFIRM', group)"></div>
        </template>
    </UFModalConfirmation>
</template>
