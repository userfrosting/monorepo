<script setup lang="ts">
import RoleManagePermissionModal from './RoleManagePermissionModal.vue'
import type { RoleInterface } from '@userfrosting/sprinkle-account/interfaces'

const { role } = defineProps<{
    role: RoleInterface
}>()
</script>

<template>
    <UFCardBox title="ROLE.PERMISSIONS">
        <UFSprunjeTable :dataUrl="'/api/roles/r/' + role.slug + '/permissions'" searchColumn="name">
            <template #actions="{ sprunjer }">
                <RoleManagePermissionModal
                    :role="role"
                    @saved="sprunjer.fetch()"
                    class="uk-button uk-button-default uk-width-1-1 uk-width-auto@s" />
            </template>

            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('PERMISSION') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="properties">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row }">
                <UFSprunjeColumn>
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.permission',
                                params: { id: row.id }
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
