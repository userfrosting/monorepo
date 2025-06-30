<script setup lang="ts">
import { useGroupDeleteApi } from '@userfrosting/sprinkle-admin/composables'
import type { GroupInterface } from '@userfrosting/sprinkle-account/interfaces'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

// Variables
const { deleteGroup } = useGroupDeleteApi()

// Props
const props = defineProps<{
    group: GroupInterface
}>()

// Emits
const emits = defineEmits(['deleted'])

// Methods
const deleteConfirmed = () => {
    deleteGroup(props.group.slug)
        .then(() => {
            emits('deleted')
        })
        .catch(() => {})
}
</script>

<template>
    <a href="#" v-bind="$attrs" :uk-toggle="'target: #confirm-group-delete-' + props.group.slug">
        <slot><font-awesome-icon icon="trash" fixed-width /> {{ $t('GROUP.DELETE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModalConfirmation
        :id="'confirm-group-delete-' + props.group.slug"
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
