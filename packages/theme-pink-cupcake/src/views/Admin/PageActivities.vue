<template>
    <UFCardBox>
        <UFSprunjeTable
            dataUrl="/api/activities"
            searchColumn="user"
            :defaultSorts="{ occurred_at: 'desc' }">
            <template #header>
                <UFSprunjeHeader sort="occurred_at">{{ $t('ACTIVITY.TIME') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="user">{{ $t('USER') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="description">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row }">
                <UFSprunjeColumn>
                    <div>{{ $tdate(row.occurred_at) }}</div>
                </UFSprunjeColumn>
                <UFSprunjeColumn v-if="row.user">
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
                    <div>
                        {{ row.ip_address }}
                    </div>
                    <div>
                        <i>{{ row.description }}</i>
                    </div>
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
