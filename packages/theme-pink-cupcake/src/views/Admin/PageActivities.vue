<script setup lang="ts">
import { ActivityDescription } from '../../components/Activities'
</script>

<template>
    <UFCardBox>
        <UFSprunjeTable
            dataUrl="/api/activities"
            searchColumn="user"
            :defaultSorts="{ occurred_at: 'desc' }">
            <template #header>
                <UFSprunjeHeader sort="occurred_at">{{ $t('ACTIVITY.TIME') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="user">{{ $t('USER') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="label">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row }">
                <UFSprunjeColumn class="uk-text-nowrap">
                    {{ $tdate(row.occurred_at) }}
                </UFSprunjeColumn>
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
                    <div class="uk-text-meta">{{ row.ip_address }}</div>
                </UFSprunjeColumn>
                <UFSprunjeColumn v-else>
                    <i>{{ $t('USER.DELETED') }}</i>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
                    <ActivityDescription :activity="row" />
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
