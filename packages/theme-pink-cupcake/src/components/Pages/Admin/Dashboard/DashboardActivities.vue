<template>
    <UFCardBox :title="$t('ACTIVITY.LATEST')">
        <UFSprunjeTable
            dataUrl="/api/activities"
            :defaultSorts="{ occurred_at: 'desc' }"
            hidePagination
            hideFilters
            hideDownload>
            <template #header>
                <UFSprunjeHeader>{{ $t('USER') }}</UFSprunjeHeader>
                <UFSprunjeHeader>{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row }">
                <UFSprunjeColumn v-if="row.user" class="uk-text-nowrap">
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.user',
                                params: { user_name: row.user.user_name }
                            }">
                            {{ row.user.full_name }} ({{ row.user.user_name }})
                        </RouterLink>
                    </strong>
                    <div class="uk-text-meta">{{ row.user.email }}</div>
                </UFSprunjeColumn>
                <UFSprunjeColumn v-else>
                    <i>{{ $t('USER.DELETED') }}</i>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
                    <div>{{ row.description }}</div>
                    <div class="uk-text-meta">{{ $tdate(row.occurred_at) }}</div>
                    <div class="uk-text-meta">{{ row.ip_address }}</div>
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
