<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePageMeta } from '@userfrosting/sprinkle-core/stores'
import { useRoleApi } from '@userfrosting/sprinkle-admin/composables'
import RoleInfo from '../../components/Pages/Admin/Role/RoleInfo.vue'
import RoleUsers from '../../components/Pages/Admin/Role/RoleUsers.vue'
import RolePermissions from '../../components/Pages/Admin/Role/RolePermissions.vue'
import type { RoleResponse } from '@userfrosting/sprinkle-admin/interfaces'

/**
 * Variables and composables
 */
const route = useRoute()
const page = usePageMeta()
const role = ref<RoleResponse>({
    id: 0,
    slug: '',
    name: '',
    description: '',
    created_at: '',
    updated_at: '',
    deleted_at: null,
    users_count: 0
})
const { fetchRole, apiError } = useRoleApi()

/**
 * Methods - Fetch group
 */
function fetch() {
    fetchRole(route.params.slug.toString()).then((fetchedRole) => {
        role.value = fetchedRole
        page.title = role.value.name
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
        <div uk-grid>
            <div class="uk-width-expand@s">
                <RoleInfo :role="role" @updated="fetch()" />
            </div>
            <div class="uk-width-2-3@s" v-if="$checkAccess('view_role_field')">
                <RoleUsers :slug="role.slug" />
            </div>
        </div>
        <div class="uk-child-width-1-1" uk-grid v-if="$checkAccess('view_role_field')">
            <div>
                <RolePermissions :role="role" />
            </div>
        </div>
    </template>
</template>
