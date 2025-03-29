<template>
    <UFSideBarItem
        v-if="$checkAccess('uri_dashboard')"
        :to="{ name: 'admin.dashboard' }"
        faIcon="gauge-high"
        :label="$t('DASHBOARD')" />
    <UFSideBarItem
        v-if="$checkAccess('uri_users')"
        :to="{ name: 'admin.users' }"
        faIcon="user"
        :label="$t('USER.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('uri_activities')"
        :to="{ name: 'admin.activities' }"
        faIcon="list-check"
        :label="$t('ACTIVITY.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('uri_roles')"
        :to="{ name: 'admin.roles' }"
        faIcon="address-card"
        :label="$t('ROLE.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('uri_permissions')"
        :to="{ name: 'admin.permissions' }"
        faIcon="key"
        :label="$t('PERMISSION.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('uri_groups')"
        :to="{ name: 'admin.groups' }"
        faIcon="users"
        :label="$t('GROUP.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('view_system_info') || $checkAccess('clear_cache')"
        :to="{ name: 'admin.config' }"
        faIcon="gear"
        :label="$t('SITE_CONFIG')" />
</template>
