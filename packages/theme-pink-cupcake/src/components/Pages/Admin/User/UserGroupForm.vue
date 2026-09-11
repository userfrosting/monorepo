<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useGroupsApi, useUserUpdateApi } from '@userfrosting/sprinkle-admin/composables'
import type { UserGroupRequest } from '@userfrosting/sprinkle-admin/interfaces'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'

const props = defineProps<{
    user: UserInterface
}>()

const emits = defineEmits(['success'])
const formData = ref<UserGroupRequest>({ group_id: props.user.group_id ?? 0 })
const { groups, loading: groupsLoading, updateGroups } = useGroupsApi()
const { submitUserGroup, apiLoading } = useUserUpdateApi()

watch(
    () => props.user,
    (user) => {
        formData.value.group_id = user.group_id ?? 0
    },
    { immediate: true }
)

onMounted(() => updateGroups())

const submitForm = async () => {
    try {
        await submitUserGroup(props.user.user_name, formData.value)
        emits('success')
    } catch {
        // The composable has already populated the shared alert state.
    }
}
</script>

<template>
    <form v-on:submit.prevent="submitForm()">
        <fieldset class="uk-fieldset uk-form-stacked">
            <div class="uk-margin">
                <label class="uk-form-label" for="user-group">{{ $t('GROUP') }}</label>
                <select
                    id="user-group"
                    class="uk-input uk-select"
                    aria-label="Group"
                    v-model="formData.group_id"
                    :disabled="groupsLoading || apiLoading">
                    <option :value="0">{{ $t('GROUP.NONE') }}</option>
                    <option v-for="group in groups" :key="group.id" :value="group.id">
                        {{ group.name }}
                    </option>
                </select>
            </div>

            <div class="uk-text-right" uk-margin>
                <button class="uk-button uk-button-default uk-modal-close" type="button">
                    {{ $t('CANCEL') }}
                </button>
                <button
                    class="uk-button uk-button-primary"
                    type="submit"
                    :disabled="groupsLoading || apiLoading">
                    {{ $t('SAVE') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
