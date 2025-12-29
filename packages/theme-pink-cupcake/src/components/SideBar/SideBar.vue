<template>
    <aside class="uf-sidebar uk-visible@m" v-bind="$attrs">
        <ul class="uk-nav-default" uk-nav>
            <slot></slot>
        </ul>
    </aside>
    <div
        class="uk-hidden@m uk-offcanvas"
        id="offcanvas-sidebar"
        uk-offcanvas="overlay: true; selClose: .uf-sidebar-close"
        v-bind="$attrs">
        <div class="uf-sidebar uk-offcanvas-bar">
            <button class="uk-offcanvas-close" type="button" uk-close></button>
            <ul class="uk-nav-default" uk-nav>
                <slot></slot>
            </ul>
        </div>
    </div>
</template>
