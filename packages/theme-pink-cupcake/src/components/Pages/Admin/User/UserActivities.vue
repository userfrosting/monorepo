<script setup lang="ts">
import { ref } from 'vue'
import { ActivityDescription } from '../../../Activities'
import type SprunjeTable from '../../../Sprunjer/SprunjeTable.vue'

/**
 * Props
 */
const props = defineProps<{
    user_name: string
}>()

/**
 * Binding to the SprunjeTable instance for refreshing activities.
 */
const activityTable = ref<InstanceType<typeof SprunjeTable> | null>(null)
function refresh() {
    activityTable.value?.fetch()
}
defineExpose({ refresh })
</script>

<template>
    <UFCardBox :title="$t('ACTIVITY', 2)">
        <UFSprunjeTable
            v-if="props.user_name !== ''"
            ref="activityTable"
            :dataUrl="'/api/users/u/' + props.user_name + '/activities'"
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
