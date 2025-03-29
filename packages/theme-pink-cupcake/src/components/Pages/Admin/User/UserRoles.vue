<script setup lang="ts">
import type { UserInterface } from '@userfrosting/sprinkle-account/interfaces'
import UserManageRolesModal from './UserManageRolesModal.vue'

const { user } = defineProps<{
    user: UserInterface
}>()
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
                    @saved="sprunjer.fetch()"
                    v-if="$checkAccess('update_user_role')"
                    class="uk-button uk-button-default" />
            </template>

            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('ROLE') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="description">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ item }">
                <UFSprunjeColumn>
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.role',
                                params: { slug: item.slug }
                            }">
                            {{ item.name }}
                        </RouterLink>
                    </strong>
                </UFSprunjeColumn>
                <UFSprunjeColumn>{{ item.description }}</UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
