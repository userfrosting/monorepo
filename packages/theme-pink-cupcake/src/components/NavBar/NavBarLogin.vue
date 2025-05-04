<script setup lang="ts">
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import NavBarDropdown from './NavBarDropdown.vue'
import FormLogin from '../Pages/Account/FormLogin.vue'
</script>

<template>
    <NavBarDropdown :label="$t('LOGIN')" class="uf-nav-login">
        <FormLogin />
        <div class="uk-text-center">
            <router-link
                class="uk-button uk-button-default"
                :to="{ name: 'account.register' }"
                data-test="gotoRegister"
                v-if="useConfigStore().get('site.registration.enabled')">
                {{ $t('REGISTER') }}
            </router-link>
            <router-link
                class="uk-button uk-button-default"
                :to="{ name: 'account.login' }"
                data-test="gotoLogin">
                {{ $t('MORE_OPTIONS') }}
            </router-link>
        </div>
        <slot></slot>
    </NavBarDropdown>
</template>
