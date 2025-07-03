<script setup lang="ts">
import { useRoute } from 'vue-router'
import { watch } from 'vue'
import { usePageMeta } from '@userfrosting/sprinkle-core/stores'
import { usePermissionApi } from '@userfrosting/sprinkle-admin/composables'
import PermissionInfo from '../../components/Pages/Admin/Permission/PermissionInfo.vue'
import PermissionUsers from '../../components/Pages/Admin/Permission/PermissionUsers.vue'

/**
 * Variables and composables
 */
const route = useRoute()
const page = usePageMeta()
const { permission, error } = usePermissionApi(() => route.params.id)

/**
 * Watcher - Match page title to the permission name
 */
watch(
    () => permission.value.id,
    () => {
        page.title = permission.value.name
    },
    { immediate: true }
)
</script>

<template>
    <template v-if="error">
        <UFErrorPage :errorCode="error.status" />
    </template>
    <template v-else>
        <div uk-grid>
            <div class="uk-width-1-3">
                <PermissionInfo :permission="permission" />
            </div>
            <div class="uk-width-2-3">
                <PermissionUsers :id="$route.params.id.toString()" />
            </div>
        </div>
    </template>
</template>
