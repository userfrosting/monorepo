<script setup lang="ts">
import { ref } from 'vue'
import { ActivityDescription } from '../../../Activities'
import type SprunjeTable from '../../../Sprunjer/SprunjeTable.vue'

const { slug } = defineProps<{
    slug: string
}>()

const activityTable = ref<InstanceType<typeof SprunjeTable> | null>(null)

function refresh() {
    activityTable.value?.fetch()
}

defineExpose({ refresh })
</script>

<template>
    <UFCardBox :title="$t('ACTIVITY', 2)">
        <UFSprunjeTable
            v-if="slug !== ''"
            ref="activityTable"
            :dataUrl="'/api/groups/g/' + slug + '/activities'"
            :defaultSorts="{ occurred_at: 'desc' }">
            <template #header>
                <UFSprunjeHeader sort="occurred_at">{{ $t('ACTIVITY.TIME') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="user">{{ $t('ACTIVITY.BY') }}</UFSprunjeHeader>
                <UFSprunjeHeader sort="label">{{ $t('ACTIVITY.DESCRIPTION') }}</UFSprunjeHeader>
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
