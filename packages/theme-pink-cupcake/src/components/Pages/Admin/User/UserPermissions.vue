<script setup lang="ts">
const { user_name } = defineProps<{
    user_name: string
}>()
</script>

<template>
    <UFCardBox :title="$t('PERMISSION', 2)">
        <UFSprunjeTable
            v-if="user_name !== ''"
            :dataUrl="'/api/users/u/' + user_name + '/permissions'"
            searchColumn="name">
            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('PERMISSION') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="properties">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
                <UFSprunjeHeader>{{ $t('PERMISSION.VIA_ROLES') }}</UFSprunjeHeader>
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
                <UFSprunjeColumn>
                    <RouterLink
                        v-for="role in row.roles_via"
                        :key="role.id"
                        :to="{ name: 'admin.role', params: { slug: role.slug } }">
                        <UFLabel>{{ role.name }}</UFLabel>
                    </RouterLink>
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
