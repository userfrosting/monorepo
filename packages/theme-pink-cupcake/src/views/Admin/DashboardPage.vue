<script lang="ts" setup>
import { useDashboardApi } from '@userfrosting/sprinkle-admin/composables'
import DashboardStats from '../../components/Pages/Admin/Dashboard/DashboardStats.vue'
import DashboardRecentUsers from '../../components/Pages/Admin/Dashboard/DashboardRecentUsers.vue'
import DashboardActivities from '../../components/Pages/Admin/Dashboard/DashboardActivities.vue'

const dashboard = useDashboardApi()
dashboard.load()
</script>

<template>
    <div class="uk-child-width-expand" uk-grid>
        <DashboardStats
            :users="dashboard.data.counter.users"
            :roles="dashboard.data.counter.roles"
            :groups="dashboard.data.counter.groups" />
    </div>
    <div class="uk-child-width-1-2" uk-grid>
        <div>
            <div class="uk-child-width-1-1" uk-grid>
                <div>
                    <DashboardRecentUsers :users="dashboard.data.users" />
                </div>
            </div>
        </div>
        <div>
            <DashboardActivities />
        </div>
    </div>
</template>
