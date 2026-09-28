<script setup lang="ts">
import UIkit from 'uikit'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import UserGroupForm from './UserGroupForm.vue'

const props = defineProps<{
    user: UserInterface
}>()

const emits = defineEmits(['saved'])

const formSuccess = () => {
    emits('saved')
    UIkit.modal('#modal-user-group-' + props.user.id).hide()
}
</script>

<template>
    <a :href="'#modal-user-group-' + props.user.id" v-bind="$attrs" uk-toggle>
        <slot> <font-awesome-icon icon="users" fixed-width /> {{ $t('GROUP.EDIT') }} </slot>
    </a>

    <UFModal :id="'modal-user-group-' + props.user.id" closable>
        <template #header>{{ $t('GROUP.EDIT') }}</template>
        <template #default>
            <UserGroupForm :user="props.user" @success="formSuccess()" />
        </template>
    </UFModal>
</template>
