<template>
    <div uk-grid>
        <div class="uk-width-1-4">
            <UFCardBox :title="$t('SITE_CONFIG')" class="uf-sidenav">
                <ul class="uk-nav uk-nav-default">
                    <UFSideBarItem
                        v-if="$checkAccess('view_system_info')"
                        :to="{ name: 'admin.config.info' }"
                        faIcon="circle-info"
                        :label="$t('SITE_CONFIG.SYSTEM_INFO')" />
                    <UFSideBarItem
                        v-if="$checkAccess('clear_cache')"
                        :to="{ name: 'admin.config.cache' }"
                        faIcon="database"
                        :label="$t('SITE_CONFIG.CACHE')" />
                    <slot />
                </ul>
            </UFCardBox>
        </div>
        <div class="uk-width-3-4">
            <RouterView />
        </div>
    </div>
</template>
