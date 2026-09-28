<script setup lang="ts">
import { useUserUpdateApi } from '@userfrosting/sprinkle-admin/composables'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

const { submitUserStatus } = useUserUpdateApi()

const props = defineProps<{
    user: UserInterface
}>()

const emits = defineEmits(['saved'])

const updateStatus = (value: '0' | '1') => {
    submitUserStatus(props.user.user_name, { flag_enabled: value }).then(() => {
        emits('saved')
    })
}
</script>

<template>
    <a
        :href="
            user.flag_enabled
                ? '#confirm-user-disable-' + props.user.id
                : '#confirm-user-enable-' + props.user.id
        "
        v-bind="$attrs"
        uk-toggle>
        <slot v-if="user.flag_enabled">
            <font-awesome-icon icon="minus-circle" fixed-width /> {{ $t('USER.DISABLE') }}
        </slot>
        <slot v-else>
            <font-awesome-icon icon="plus-circle" fixed-width /> {{ $t('USER.ENABLE') }}
        </slot>
    </a>

    <UFModalConfirmation
        v-if="user.flag_enabled"
        :id="'confirm-user-disable-' + props.user.id"
        title="USER.DISABLE"
        @confirmed="updateStatus('0')"
        acceptLabel="USER.DISABLE"
        acceptIcon="check"
        :rejectIcon="null"
        :acceptSeverity="Severity.Success">
        <template #prompt>
            <div v-html="$t('USER.DISABLE_CONFIRM', props.user)"></div>
        </template>
    </UFModalConfirmation>

    <UFModalConfirmation
        v-else
        :id="'confirm-user-enable-' + props.user.id"
        title="USER.ENABLE"
        @confirmed="updateStatus('1')"
        acceptLabel="USER.ENABLE"
        acceptIcon="check"
        :rejectIcon="null"
        :acceptSeverity="Severity.Success">
        <template #prompt>
            <div v-html="$t('USER.ENABLE_CONFIRM', props.user)"></div>
        </template>
    </UFModalConfirmation>
</template>
