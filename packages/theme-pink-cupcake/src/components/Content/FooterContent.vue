<template>
    <footer class="uk-section uk-section-small uk-section-muted uk-text-center">
        <hr />
        <p class="uk-text-small uk-text-center" data-test="slot">
            <slot></slot>
            <slot name="userfrosting">
                <span v-if="$slots.default"> | </span>
                <span v-html="$t('BUILT_WITH_UF')"></span>
                <span>
                    | {{ $t('THEME_BY') }}
                    <a
                        href="http://getuikit.com"
                        title="Visit UIkit 3 site"
                        target="_blank"
                        data-uk-tooltip>
                        <span data-uk-icon="uikit"></span>
                    </a>
                </span>
            </slot>
        </p>
    </footer>
</template>
