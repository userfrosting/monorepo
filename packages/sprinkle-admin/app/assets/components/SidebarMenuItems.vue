<template>
    <UFSideBarItem :to="{ name: 'admin.dashboard' }" faIcon="gauge-high" :label="$t('DASHBOARD')" />
    <UFSideBarItem :to="{ name: 'admin.users' }" faIcon="user" :label="$t('USER.PAGE')" />
    <UFSideBarItem
        :to="{ name: 'admin.activities' }"
        faIcon="list-check"
        :label="$t('ACTIVITY.PAGE')" />
    <UFSideBarItem :to="{ name: 'admin.roles' }" faIcon="address-card" :label="$t('ROLE.PAGE')" />
    <UFSideBarItem
        :to="{ name: 'admin.permissions' }"
        faIcon="key"
        :label="$t('PERMISSION.PAGE')" />
    <UFSideBarItem :to="{ name: 'admin.groups' }" faIcon="users" :label="$t('GROUP.PAGE')" />
    <UFSideBarItem :to="{ name: 'admin.config' }" faIcon="gear" :label="$t('SITE_CONFIG')" />
</template>
