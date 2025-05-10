<script setup lang="ts">
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

const { slug } = defineProps<{
    slug: string
}>()
</script>

<template>
    <UFCardBox title="GROUP.USERS">
        <UFSprunjeTable
            :dataUrl="'/api/groups/g/' + slug + '/users'"
            searchColumn="name"
            hideFilters>
            <template #header>
                <UFSprunjeHeader sort="name">{{ $t('USER') }}</UFSprunjeHeader>
                <UFSprunjeHeader>{{ $t('STATUS') }}</UFSprunjeHeader>
            </template>

            <template #body="{ item }">
                <UFSprunjeColumn>
                    <strong>
                        <RouterLink
                            :to="{
                                name: 'admin.user',
                                params: { user_name: item.user_name }
                            }">
                            {{ item.full_name }} ({{ item.user_name }})
                        </RouterLink>
                    </strong>
                    <div class="uk-text-meta">{{ item.email }}</div>
                </UFSprunjeColumn>
                <UFSprunjeColumn>
                    <UFLabel :severity="Severity.Danger" v-if="item.flag_enabled == false">
                        {{ $t('DISABLED') }}
                    </UFLabel>
                    <UFLabel :severity="Severity.Success" v-else>
                        {{ $t('ENABLED') }}
                    </UFLabel>
                </UFSprunjeColumn>
            </template>
        </UFSprunjeTable>
    </UFCardBox>
</template>
