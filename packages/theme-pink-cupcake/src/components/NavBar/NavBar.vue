<script setup lang="ts">
import AppLink from '../Content/AppLink.vue'

withDefaults(
    defineProps<{
        to?: string | { name: string }
        title?: string
    }>(),
    {
        to: '',
        title: ''
    }
)
</script>

<template>
    <header>
        <div uk-sticky="sel-target: .uk-navbar-container; cls-active: uk-navbar-sticky">
            <div class="uk-navbar-container">
                <div class="uk-container uk-container-expand">
                    <nav class="uk-navbar" uk-navbar>
                        <ul class="uk-navbar-nav uk-hidden@m">
                            <li>
                                <a
                                    class="uk-navbar-item uk-logo"
                                    uk-toggle="target: aside.uf-sidenav; cls: open">
                                    <font-awesome-icon icon="bars" />
                                </a>
                            </li>
                        </ul>
                        <div class="uk-navbar-left" data-test="navbar-left">
                            <AppLink :to="to" class="uk-navbar-item uk-logo" label="">
                                <slot name="title">{{ title }}</slot>
                            </AppLink>
                        </div>
                        <div class="uk-navbar-right" data-test="navbar-right">
                            <ul class="uk-navbar-nav">
                                <slot></slot>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
</template>
