<template>
    <div uk-grid>
        <div class="uk-width-1-4@s">
            <UFCardBox :title="$t('ACCOUNT.MY')">
                <ul class="uk-nav uk-nav-default">
                    <UFSideBarItem
                        :to="{ name: 'account.settings.profile' }"
                        faIcon="user"
                        :label="$t('ACCOUNT.SETTINGS.PERSONAL')" />
                    <UFSideBarItem
                        :to="{ name: 'account.settings.email' }"
                        faIcon="envelope"
                        :label="$t('ACCOUNT.SETTINGS.EMAIL')" />
                    <UFSideBarItem
                        :to="{ name: 'account.settings.password' }"
                        faIcon="lock"
                        :label="$t('ACCOUNT.SETTINGS.PASSWORD')" />
                    <slot />
                </ul>
            </UFCardBox>
        </div>
        <div class="uk-width-3-4@s">
            <RouterView />
        </div>
    </div>
</template>
