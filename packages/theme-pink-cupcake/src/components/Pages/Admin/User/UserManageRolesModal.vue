<script setup lang="ts">
import { computed } from 'vue'
import UIkit from 'uikit'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import { useUserRolesApi, useUserUpdateApi } from '@userfrosting/sprinkle-admin/composables'

/**
 * Emits - Define the saved event. This event is emitted when the form is saved
 * to notify the parent component to refresh the data.
 */
const emits = defineEmits(['saved'])

/**
 * Props - The user to edit.
 */
const { user } = defineProps<{
    user: UserInterface
}>()

/**
 * Methods - Fetch roles, fetch user's roles and submit the form.
 */
const { loading, selected, roles, fetch } = useUserRolesApi()
const { submitUserUpdate } = useUserUpdateApi()
const submitForm = () => {
    submitUserUpdate(user.user_name, 'roles', { roles: selected.value }).then(() => {
        // Emit the saved event
        emits('saved')

        // Close the modal
        UIkit.modal('#' + modalName.value).hide()
    })
}

/**
 * Computed - Check if all roles are selected and handle change of the
 * selectAll checkbox.
 */
const allSelected = computed({
    get: () => (roles.value ? selected.value.length == roles.value.length : false),
    set: (allSelected: string) => {
        selected.value.splice(0)
        if (allSelected) {
            roles.value.forEach(function (role) {
                selected.value.push(role.id)
            })
        }
    }
})

const modalName = computed(() => 'modal-user-manage-roles-' + user.user_name)
</script>

<template>
    <a v-bind="$attrs" :uk-toggle="'target: #' + modalName" @click="fetch(user.user_name)">
        <slot><font-awesome-icon icon="address-card" /> {{ $t('ROLE.MANAGE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModal :id="modalName" class="uk-modal-container" closable>
        <template #header>{{ $t('ROLE.MANAGE') }} - {{ user.full_name }}</template>
        <template #default>
            <p>{{ $t('ROLE.MANAGE_EXPLAIN') }}</p>
            <div class="uk-text-center" v-if="loading">
                <font-awesome-icon icon="spinner" spin size="2xl" />
            </div>
            <form
                v-on:submit.prevent="submitForm()"
                v-if="!loading"
                class="uk-overflow-auto uk-height-max-large">
                <fieldset class="uk-fieldset uk-form-stacked">
                    <table class="uk-table uk-table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <input
                                        class="uk-checkbox"
                                        type="checkbox"
                                        v-model="allSelected" />
                                </th>
                                <th>{{ $t('ROLE') }}</th>
                                <th>{{ $t('DESCRIPTION') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="role in roles" :key="role.id">
                                <td>
                                    <input
                                        class="uk-checkbox"
                                        type="checkbox"
                                        :value="role.id"
                                        v-model="selected" />
                                </td>
                                <td>{{ role.name }}</td>
                                <td>{{ role.description }}</td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            </form>
        </template>
        <template #footer>
            <button class="uk-button uk-button-default uk-modal-close" type="button">
                {{ $t('CANCEL') }}
            </button>
            <button
                class="uk-button uk-button-primary"
                type="submit"
                @click="submitForm()"
                :disabled="loading">
                {{ $t('ROLE.UPDATE') }}
            </button>
        </template>
    </UFModal>
</template>
