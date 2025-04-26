<script setup lang="ts">
import NavBarDropdown from './NavBarDropdown.vue'
import FormLogin from '../Pages/Account/FormLogin.vue'
</script>

<template>
    <NavBarDropdown :label="$t('LOGIN')" class="uf-nav-login uk-text-center">
        <FormLogin />
        <div class="uk-text-center">
            <button
                class="uk-button uk-button-default"
                @click="$emit('gotoRegistration')"
                data-test="gotoRegistration">
                {{ $t('REGISTER') }}
            </button>
            <button
                class="uk-button uk-button-default"
                @click="$emit('gotoLogin')"
                data-test="gotoLogin">
                {{ $t('MORE_OPTIONS') }}
            </button>
        </div>
        <slot></slot>
    </NavBarDropdown>
</template>
