<script lang="ts" setup>
import { useDashboardApi } from '@userfrosting/sprinkle-admin/composables'
import DashboardRecentUsers from '../../components/Pages/Admin/Dashboard/DashboardRecentUsers.vue'
import DashboardActivities from '../../components/Pages/Admin/Dashboard/DashboardActivities.vue'

const dashboard = useDashboardApi()
dashboard.load()
</script>

<template>
    <div class="uk-child-width-expand@s" uk-grid>
        <UFInfoBox
            v-if="$checkAccess('uri_users')"
            :value="dashboard.data.counter.users"
            :label="$t('USER', dashboard.data.counter.users)"
            faIcon="user"
            :to="{ name: 'admin.users' }" />
        <UFInfoBox
            v-if="$checkAccess('uri_roles')"
            :value="dashboard.data.counter.roles"
            :label="$t('ROLE', dashboard.data.counter.roles)"
            faIcon="address-card"
            :to="{ name: 'admin.roles' }" />
        <UFInfoBox
            v-if="$checkAccess('uri_groups')"
            :value="dashboard.data.counter.groups"
            :label="$t('GROUP', dashboard.data.counter.groups)"
            faIcon="users"
            :to="{ name: 'admin.groups' }" />
    </div>
    <div class="uk-child-width-1-2@s" uk-grid>
        <div v-if="$checkAccess('uri_users')">
            <div class="uk-child-width-1-1" uk-grid>
                <div>
                    <DashboardRecentUsers :users="dashboard.data.users" />
                </div>
            </div>
        </div>
        <div v-if="$checkAccess('uri_activities')">
            <DashboardActivities />
        </div>
    </div>
</template>
