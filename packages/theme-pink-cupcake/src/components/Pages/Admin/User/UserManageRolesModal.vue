<script setup lang="ts">
import { computed } from 'vue'
import UIkit from 'uikit'
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import type { Sprunjer } from '@userfrosting/sprinkle-core/interfaces'
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
const { loading, selected, fetch } = useUserRolesApi()
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
 * Helpers - Compute and toggle "select all" using the Sprunje state.
 */
const allSelected = (sprunje: Sprunjer): boolean => {
    const filteredRows = sprunje.rows.value

    return filteredRows.length > 0 && filteredRows.every((role) => selected.value.includes(role.id))
}

const setAllSelected = (allSelected: boolean, sprunje: Sprunjer): void => {
    // To unselect all
    if (!allSelected) {
        selected.value.splice(0)

        return
    }

    // To select all, based on currently loaded rows. Includes filters.
    selected.value = sprunje.rows.value.map((role) => role.id)
}

const modalName = computed(() => 'modal-user-manage-roles-' + user.id)
</script>

<template>
    <a v-bind="$attrs" uk-toggle :href="'#' + modalName" @click="fetch(user.user_name)">
        <slot><font-awesome-icon icon="address-card" /> {{ $t('ROLE.MANAGE') }}</slot>
    </a>

    <!-- This is the modal -->
    <UFModal :id="modalName" class="uk-modal-container" closable>
        <template #header>{{ $t('ROLE.MANAGE') }} - {{ user.full_name }}</template>
        <template #default>
            <p>{{ $t('ROLE.MANAGE_EXPLAIN') }}</p>

            <UFSprunjeTable
                dataUrl="/api/roles"
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
                    <UFSprunjeHeader sort="name">{{ $t('ROLE') }}</UFSprunjeHeader>
                    <UFSprunjeHeader sort="description">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
                </template>

                <template #body="{ row }">
                    <UFSprunjeColumn>
                        <input
                            class="uk-checkbox"
                            type="checkbox"
                            :value="Number(row.id)"
                            v-model="selected" />
                    </UFSprunjeColumn>
                    <UFSprunjeColumn>{{ row.name }}</UFSprunjeColumn>
                    <UFSprunjeColumn>{{ row.description }}</UFSprunjeColumn>
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
                {{ $t('ROLE.UPDATE') }}
            </button>
        </template>
    </UFModal>
</template>
