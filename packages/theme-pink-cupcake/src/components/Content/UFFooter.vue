<template>
    <footer class="uk-section uk-section-small uk-section-muted">
        <hr />
        <div
            class="uk-text-small uk-grid-divider uk-grid-small uk-child-width-auto uk-flex-center"
            uk-grid
            data-test="slot">
            <slot></slot>
            <slot name="userfrosting">
                <div v-html="$t('BUILT_WITH_UF')"></div>
                <div>
                    {{ $t('THEME_BY') }}
                    <a
                        href="http://getuikit.com"
                        title="Visit UIkit 3 site"
                        target="_blank"
                        data-uk-tooltip>
                        <span data-uk-icon="uikit"></span>
                    </a>
                </div>
            </slot>
        </div>
    </footer>
</template>
