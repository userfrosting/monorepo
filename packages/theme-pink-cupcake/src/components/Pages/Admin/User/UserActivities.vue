<script setup lang="ts">
const { user_name } = defineProps<{
    user_name: string
}>()
</script>

<template>
    <UFCardBox :title="$t('ACTIVITY', 2)">
        <UFSprunjeTable
            v-if="user_name !== ''"
            :dataUrl="'/api/users/u/' + user_name + '/activities'"
            :defaultSorts="{ occurred_at: 'desc' }">
            <template #header>
                <UFSprunjeHeader sort="occurred_at">{{ $t('ACTIVITY.TIME') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="description">{{ $t('DESCRIPTION') }}</UFSprunjeHeader>
            </template>

            <template #body="{ row }">
                <UFSprunjeColumn>
                    <div>{{ $tdate(row.occurred_at) }}</div>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
                    <div>{{ row.description }}</div>
                    <div class="uk-text-meta">{{ row.ip_address }}</div>
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
