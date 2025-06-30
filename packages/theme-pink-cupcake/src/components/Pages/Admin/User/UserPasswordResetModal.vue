<script setup lang="ts">
import { useUserPasswordResetApi } from '@userfrosting/sprinkle-admin/composables'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

// Variables
const { passwordReset } = useUserPasswordResetApi()

// Props
const props = defineProps<{
    user: UserInterface
}>()

// Methods
const confirmed = () => {
    passwordReset(props.user.user_name)
}
</script>

<template>
    <a
        href="#"
        v-bind="$attrs"
        :uk-toggle="'target: #confirm-user-reset-password-' + props.user.user_name">
        <slot>
            <font-awesome-icon icon="key" fixed-width />
            {{ $t('USER.ADMIN.PASSWORD_RESET') }}
        </slot>
    </a>

    <!-- This is the modal -->
    <UFModalConfirmation
        :id="'confirm-user-reset-password-' + props.user.user_name"
        :title="$t('PASSWORD.RESET')"
        @confirmed="confirmed()"
        :acceptLabel="$t('YES')"
        acceptIcon="check"
        :rejectIcon="null"
        :acceptSeverity="Severity.Success">
        <template #prompt>
            <div v-html="$t('USER.ADMIN.PASSWORD_RESET_CONFIRM', user)"></div>
        </template>
    </UFModalConfirmation>
</template>
