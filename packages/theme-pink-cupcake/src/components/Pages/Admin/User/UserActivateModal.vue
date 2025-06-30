<script setup lang="ts">
import { useUserUpdateApi } from '@userfrosting/sprinkle-admin/composables'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

// Variables
const { submitUserUpdate } = useUserUpdateApi()

// Props
const props = defineProps<{
    user: UserInterface
}>()

// Emits
const emits = defineEmits(['saved'])

// Methods
const updateUser = (fieldName: string, value: string) => {
    // Assign the field name and value to the payload
    const payload: Record<string, any> = {}
    payload[fieldName] = value

    submitUserUpdate(props.user.user_name, fieldName, payload).then(() => {
        emits('saved')
    })
}
</script>

<template>
    <a
        href="#"
        v-bind="$attrs"
        :uk-toggle="'target: #confirm-user-activate-' + props.user.user_name"
        v-if="user.flag_verified == false">
        <slot><font-awesome-icon icon="bolt" fixed-width /> {{ $t('USER.ACTIVATE') }}</slot>
    </a>

    <a
        href="#"
        v-bind="$attrs"
        :uk-toggle="'target: #confirm-user-disable-' + props.user.user_name"
        v-else-if="user.flag_enabled">
        <slot><font-awesome-icon icon="minus-circle" fixed-width /> {{ $t('USER.DISABLE') }}</slot>
    </a>

    <a
        href="#"
        v-bind="$attrs"
        :uk-toggle="'target: #confirm-user-enable-' + props.user.user_name"
        v-else>
        <slot><font-awesome-icon icon="plus-circle" fixed-width /> {{ $t('USER.ENABLE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModalConfirmation
        :id="'confirm-user-activate-' + props.user.user_name"
        title="USER.ACTIVATE"
        @confirmed="updateUser('flag_verified', '1')"
        acceptLabel="USER.ACTIVATE"
        acceptIcon="check"
        :rejectIcon="null"
        :acceptSeverity="Severity.Success">
        <template #prompt>
            <div v-html="$t('USER.ACTIVATE_CONFIRM', props.user)"></div>
        </template>
    </UFModalConfirmation>

    <UFModalConfirmation
        :id="'confirm-user-disable-' + props.user.user_name"
        title="USER.DISABLE"
        @confirmed="updateUser('flag_enabled', '0')"
        acceptLabel="USER.DISABLE"
        acceptIcon="check"
        :rejectIcon="null"
        :acceptSeverity="Severity.Success">
        <template #prompt>
            <div v-html="$t('USER.DISABLE_CONFIRM', props.user)"></div>
        </template>
    </UFModalConfirmation>

    <UFModalConfirmation
        :id="'confirm-user-enable-' + props.user.user_name"
        title="USER.ENABLE"
        @confirmed="updateUser('flag_enabled', '1')"
        acceptLabel="USER.ENABLE"
        acceptIcon="check"
        :rejectIcon="null"
        :acceptSeverity="Severity.Success">
        <template #prompt>
            <div v-html="$t('USER.ENABLE_CONFIRM', props.user)"></div>
        </template>
    </UFModalConfirmation>
</template>
