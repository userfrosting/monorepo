<script setup lang="ts">
import { useRoute } from 'vue-router'
import { watch } from 'vue'
import { usePageMeta } from '@userfrosting/sprinkle-core/stores'
import { useGroupApi } from '@userfrosting/sprinkle-admin/composables'
import GroupInfo from '../../components/Pages/Admin/Group/GroupInfo.vue'
import GroupUsers from '../../components/Pages/Admin/Group/GroupUsers.vue'

/**
 * Variables and composables
 */
const route = useRoute()
const page = usePageMeta()
const { group, error, fetchGroup } = useGroupApi(() => route.params.slug)

/**
 * Watcher - Match page title to the group name
 */
watch(
    () => group.value.slug,
    () => {
        page.title = group.value.name
    },
    { immediate: true }
)
</script>

<template>
    <template v-if="error">
        <UFErrorPage :errorCode="error.status" />
    </template>
    <template v-else>
        <div class="uk-child-width-expand" uk-grid>
            <div>
                <GroupInfo :group="group" @groupUpdated="fetchGroup()" />
            </div>
            <div class="uk-width-2-3" v-if="$checkAccess('view_group_field')">
                <GroupUsers :slug="$route.params.slug.toString()" />
            </div>
        </div>
    </template>
</template>
