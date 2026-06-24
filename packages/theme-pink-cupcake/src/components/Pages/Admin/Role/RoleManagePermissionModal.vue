<script setup lang="ts">
import { computed } from 'vue'
import UIkit from 'uikit'
import type { RoleInterface } from '@userfrosting/sprinkle-account/interfaces'
import type { Sprunjer } from '@userfrosting/sprinkle-core/interfaces'
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
const { loading, selected, fetch } = useRolePermissionsApi()
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
 * Helpers - Compute and toggle "select all" using the Sprunje state.
 */
const allSelected = (sprunje: Sprunjer): boolean => {
    const filteredRows = sprunje.rows.value

    return (
        filteredRows.length > 0 &&
        filteredRows.every((permission) => selected.value.includes(permission.id))
    )
}

const setAllSelected = (allSelected: boolean, sprunje: Sprunjer): void => {
    // To unselect all
    if (!allSelected) {
        selected.value.splice(0)

        return
    }

    // To select all, based on currently loaded rows. Includes filters.
    selected.value = sprunje.rows.value.map((permission) => permission.id)
}

const modalName = computed(() => 'modal-role-manage-permission-' + role.id)
</script>

<template>
    <a v-bind="$attrs" uk-toggle :href="'#' + modalName" @click="fetch(role.slug)">
        <slot><font-awesome-icon icon="key" /> {{ $t('PERMISSION.ASSIGN') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModal :id="modalName" class="uk-modal-container" closable>
        <template #header>{{ $t('PERMISSION.ASSIGN') }} - {{ role.name }}</template>
        <template #default>
            <p>{{ $t('PERMISSION.ASSIGN.EXPLAIN') }}</p>

            <UFSprunjeTable
                dataUrl="/api/permissions"
                searchColumn="name"
                defaultSize="all"
                hideDownload
                hidePagination>
                <template #header="{ sprunjer }">
                    <UFSprunjeHeader>
                        <input
                            class="uk-checkbox"
                            type="checkbox"
                            :checked="allSelected(sprunjer)"
                            @change="
                                setAllSelected(
                                    ($event.target as HTMLInputElement).checked,
                                    sprunjer
                                )
                            " />
                    </UFSprunjeHeader>
                    <UFSprunjeHeader sort="name">{{ $t('PERMISSION') }}</UFSprunjeHeader>
                    <UFSprunjeHeader sort="properties">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
                </template>

                <template #body="{ row }">
                    <UFSprunjeColumn>
                        <input
                            class="uk-checkbox"
                            type="checkbox"
                            :value="row.id"
                            v-model="selected" />
                    </UFSprunjeColumn>
                    <UFSprunjeColumn>{{ row.name }}</UFSprunjeColumn>
                    <UFSprunjeColumn>
                        {{ row.description }}
                        <div>
                            <code>{{ row.slug }}</code>
                        </div>
                        <div>
                            ↳
                            <code style="white-space: normal">
                                {{ row.conditions }}
                            </code>
                        </div>
                    </UFSprunjeColumn>
                </template>
            </UFSprunjeTable>
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
