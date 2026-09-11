<script setup lang="ts">
import { useUserUpdateApi } from '@userfrosting/sprinkle-admin/composables'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

const { submitUserVerification } = useUserUpdateApi()

const props = defineProps<{
    user: UserInterface
}>()

const emits = defineEmits(['saved'])

const verifyUser = () => {
    submitUserVerification(props.user.user_name, { flag_verified: '1' }).then(() => {
        emits('saved')
    })
}
</script>

<template>
    <a :href="'#confirm-user-verify-' + props.user.id" v-bind="$attrs" uk-toggle>
        <slot><font-awesome-icon icon="bolt" fixed-width /> {{ $t('USER.ACTIVATE') }}</slot>
    </a>

    <UFModalConfirmation
        :id="'confirm-user-verify-' + props.user.id"
        title="USER.ACTIVATE"
        @confirmed="verifyUser()"
        acceptLabel="USER.ACTIVATE"
        acceptIcon="check"
        :rejectIcon="null"
        :acceptSeverity="Severity.Success">
        <template #prompt>
            <div v-html="$t('USER.ACTIVATE_CONFIRM', props.user)"></div>
        </template>
    </UFModalConfirmation>
</template>
