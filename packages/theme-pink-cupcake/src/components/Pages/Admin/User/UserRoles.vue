<script setup lang="ts">
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import UserManageRolesModal from './UserManageRolesModal.vue'

/**
 * Emits
 */
const emits = defineEmits(['updated'])

/**
 * Props
 */
const { user } = defineProps<{
    user: UserInterface
}>()

/**
 * Methods - Action to take when roles are updated
 */
const rolesUpdated = (fetch: () => void) => {
    fetch()
    emits('updated')
}
</script>

<template>
    <UFCardBox :title="$t('ROLE', 2)">
        <UFSprunjeTable
            v-if="user.user_name !== ''"
            :dataUrl="'/api/users/u/' + user.user_name + '/roles'"
            searchColumn="name"
            hideFilters>
            <template #actions="{ sprunjer }">
                <UserManageRolesModal
                    :user="user"
                    @saved="rolesUpdated(sprunjer.fetch)"
                    v-if="$checkAccess('update_user_role')"
                    class="uk-button uk-button-default uk-width-1-1 uk-width-auto@s" />
            </template>

            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('ROLE') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="description">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row }">
                <UFSprunjeColumn class="uk-text-nowrap">
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.role',
                                params: { slug: row.slug }
                            }">
                            {{ row.name }}
                        </RouterLink>
                    </strong>
                </UFSprunjeColumn>
                <UFSprunjeColumn>{{ row.description }}</UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
