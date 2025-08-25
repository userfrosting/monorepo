<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePageMeta } from '@userfrosting/sprinkle-core/stores'
import { useUserApi } from '@userfrosting/sprinkle-admin/composables'
import UserInfo from '../../components/Pages/Admin/User/UserInfo.vue'
import UserActivities from '../../components/Pages/Admin/User/UserActivities.vue'
import UserRoles from '../../components/Pages/Admin/User/UserRoles.vue'
import UserPermissions from '../../components/Pages/Admin/User/UserPermissions.vue'
import type { UserResponse } from '@userfrosting/sprinkle-admin/interfaces'

/**
 * Variables and composables
 */
const route = useRoute()
const page = usePageMeta()
const user = ref<UserResponse>({
    id: 0,
    user_name: '',
    first_name: '',
    last_name: '',
    full_name: '',
    email: '',
    avatar: '',
    flag_enabled: false,
    flag_verified: false,
    group_id: null,
    locale: '',
    created_at: '',
    updated_at: '',
    deleted_at: null,
    locale_name: '',
    group: null
})
const { apiError, fetchUser } = useUserApi()

/**
 * Methods - Fetch user
 */
function fetch() {
    fetchUser(route.params.user_name.toString()).then((fetchedUser) => {
        user.value = fetchedUser
        page.title = user.value.full_name
    })
}

/**
 * Watcher - Match page title to the user full name
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
                <UserInfo :user="user" @updated="fetch()" />
            </div>
            <div class="uk-width-2-3" v-if="$checkAccess('view_user_roles')">
                <UserRoles :user="user" />
            </div>
        </div>

        <div class="uk-child-width-1-1" uk-grid>
            <div v-if="$checkAccess('view_user_permissions')">
                <UserPermissions :user_name="user.user_name" />
            </div>
            <div v-if="$checkAccess('view_user_activities')">
                <UserActivities :user_name="user.user_name" />
            </div>
        </div>
    </template>
</template>
