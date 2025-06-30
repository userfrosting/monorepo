<script setup lang="ts">
import { computed } from 'vue'
import UIkit from 'uikit'
import type { RoleInterface } from '@userfrosting/sprinkle-account/interfaces'
import { useRolePermissionsApi, useRoleUpdateApi } from '@userfrosting/sprinkle-admin/composables'

/**
 * Emits - Define the saved event. This event is emitted when the form is saved
 * to notify the parent component to refresh the data.
 */
const emits = defineEmits(['saved'])

/**
 * Props - The role to edit.
 */
const { role } = defineProps<{
    role: RoleInterface
}>()

/**
 * Methods - Fetch permissions, selected, fetch method and submit the form.
 */
const { loading, selected, permissions, fetch } = useRolePermissionsApi()
const { submitRoleUpdate } = useRoleUpdateApi()
const submitForm = () => {
    submitRoleUpdate(role.slug, 'permissions', { permissions: selected.value })
        .then(() => {
            // Emit the saved event
            emits('saved')

            // Close the modal
            UIkit.modal('#' + modalName.value).hide()
        })
        .catch(() => {})
}

/**
 * Computed - Check if all permissions are selected and handle change of the
 * selectAll checkbox.
 */
const allSelected = computed({
    get: () => (permissions.value ? selected.value.length == permissions.value.length : false),
    set: (allSelected: string) => {
        selected.value.splice(0)
        if (allSelected) {
            permissions.value.forEach(function (permission) {
                selected.value.push(permission.id)
            })
        }
    }
})

const modalName = computed(() => 'modal-role-manage-permission-' + role.slug)
</script>

<template>
    <a v-bind="$attrs" :uk-toggle="'target: #' + modalName" @click="fetch(role.slug)">
        <slot><font-awesome-icon icon="key" /> {{ $t('PERMISSION.ASSIGN') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModal :id="modalName" class="uk-modal-container" closable>
        <template #header>{{ $t('PERMISSION.ASSIGN') }} - {{ role.name }}</template>
        <template #default>
            <p>{{ $t('PERMISSION.ASSIGN.EXPLAIN') }}</p>
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
                                <th>{{ $t('PERMISSION') }}</th>
                                <th>{{ $t('DESCRIPTION') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="permission in permissions" :key="permission.id">
                                <td>
                                    <input
                                        class="uk-checkbox"
                                        type="checkbox"
                                        :value="permission.id"
                                        v-model="selected" />
                                </td>
                                <td>{{ permission.name }}</td>
                                <td>
                                    {{ permission.description }}
                                    <div>
                                        <code>{{ permission.slug }}</code>
                                    </div>
                                    <div>
                                        ↳
                                        <code style="white-space: normal">{{
                                            permission.conditions
                                        }}</code>
                                    </div>
                                </td>
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
                {{ $t('PERMISSION.UPDATE') }}
            </button>
        </template>
    </UFModal>
</template>
