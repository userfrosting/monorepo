<script setup lang="ts">
const { slug } = defineProps<{
    slug: string
}>()
</script>

<template>
    <UFCardBox title="ROLE.USERS">
        <UFSprunjeTable
            :dataUrl="'/api/roles/r/' + slug + '/users'"
            searchColumn="name"
            hideFilters>
            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('USER') }}</UFSprunjeHeader>
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
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
