<script setup lang="ts">
import { ActivityDescription } from '../../../Activities'

const { slug } = defineProps<{
    slug: string
}>()
</script>

<template>
    <UFCardBox :title="$t('ACTIVITY', 2)">
        <UFSprunjeTable
            v-if="slug !== ''"
            :dataUrl="'/api/roles/r/' + slug + '/activities'"
            :defaultSorts="{ occurred_at: 'desc' }">
            <template #header>
                <UFSprunjeHeader sort="occurred_at">{{ $t('ACTIVITY.TIME') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="user">{{ $t('USER') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="label">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row }">
                <UFSprunjeColumn>
                    <div>{{ $tdate(row.occurred_at) }}</div>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
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
                <UFSprunjeColumn>
                    <ActivityDescription :activity="row" />
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
