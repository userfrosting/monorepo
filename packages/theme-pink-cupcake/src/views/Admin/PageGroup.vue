<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePageMeta } from '@userfrosting/sprinkle-core/stores'
import { useGroupApi } from '@userfrosting/sprinkle-admin/composables'
import GroupInfo from '../../components/Pages/Admin/Group/GroupInfo.vue'
import GroupUsers from '../../components/Pages/Admin/Group/GroupUsers.vue'
import type { GroupResponse } from '@userfrosting/sprinkle-admin/interfaces'

/**
 * Variables and composables
 */
const route = useRoute()
const page = usePageMeta()
const group = ref<GroupResponse>({
    id: 0,
    name: '',
    slug: '',
    description: '',
    icon: '',
    created_at: '',
    updated_at: '',
    deleted_at: null,
    users_count: 0
})
const { fetchGroup, apiError } = useGroupApi()

/**
 * Methods - Fetch group
 */
function fetch() {
    fetchGroup(route.params.slug.toString()).then((fetchedGroup) => {
        group.value = fetchedGroup
        page.title = group.value.name
    })
}

/**
 * Watcher - Update page on slug change
 */
watch(
    () => route.params.slug,
    () => fetch(),
    { immediate: true }
)
</script>

<template>
    <template v-if="apiError">
        <UFErrorPage :errorCode="apiError.status" />
    </template>
    <template v-else>
        <div class="uk-child-width-expand" uk-grid>
            <div>
                <GroupInfo :group="group" @groupUpdated="fetch()" />
            </div>
            <div class="uk-width-2-3" v-if="$checkAccess('view_group_field')">
                <GroupUsers :slug="$route.params.slug.toString()" />
            </div>
        </div>
    </template>
</template>
