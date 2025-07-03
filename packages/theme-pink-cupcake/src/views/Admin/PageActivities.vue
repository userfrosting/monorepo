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

            <template #body="{ item }">
                <UFSprunjeColumn>
                    <div>{{ $tdate(item.occurred_at) }}</div>
                </UFSprunjeColumn>
                <UFSprunjeColumn v-if="item.user">
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.user',
                                params: { user_name: item.user.user_name }
                            }">
                            {{ item.user.full_name }} ({{ item.user.user_name }})
                        </RouterLink>
                    </strong>
                    <div class="uk-text-meta">{{ item.user.email }}</div>
                </UFSprunjeColumn>
                <UFSprunjeColumn v-else>
                    <i>{{ $t('USER.DELETED') }}</i>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
                    <div>
                        {{ item.ip_address }}
                    </div>
                    <div>
                        <i>{{ item.description }}</i>
                    </div>
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
