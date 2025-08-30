<script setup lang="ts">
const { id } = defineProps<{
    id: string | number
}>()
</script>

<template>
    <UFCardBox title="PERMISSION.USERS">
        <UFSprunjeTable
            :dataUrl="'/api/permissions/p/' + id + '/users'"
            searchColumn="name"
            hideFilters>
            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('USER') }}</UFSprunjeHeader>
                <UFSprunjeHeader>{{ $t('PERMISSION.VIA_ROLES') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row }">
                <UFSprunjeColumn>
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.user',
                                params: { user_name: row.user_name }
                            }">
                            {{ row.full_name }} ({{ row.user_name }})
                        </RouterLink>
                    </strong>
                    <div class="uk-text-meta">{{ row.email }}</div>
                </UFSprunjeColumn>
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
