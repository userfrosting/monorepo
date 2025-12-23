<template>
    <aside class="uf-sidebar uk-visible@s">
        <ul class="uk-nav-default" uk-nav>
            <slot></slot>
        </ul>
    </aside>
    <div class="uk-hidden@s uk-offcanvas" id="offcanvas-sidebar" uk-offcanvas="overlay: true; selClose: .uf-sidebar-close">
        <div class="uf-sidebar uk-offcanvas-bar">
            <button class="uk-offcanvas-close" type="button" uk-close></button>
            <ul class="uk-nav-default" uk-nav>
                <slot></slot>
            </ul>
        </div>
    </div>
</template>
