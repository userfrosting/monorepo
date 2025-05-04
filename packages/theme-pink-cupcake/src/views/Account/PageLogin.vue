<script setup lang="ts">
import { useConfigStore, usePageMeta } from '@userfrosting/sprinkle-core/stores'
import FormLogin from '../../components/Pages/Account/FormLogin.vue'
usePageMeta().hideTitle = true
</script>

<template>
    <UFCardBoxHalf>
        <h3 class="uk-card-title">{{ $t('LOGIN.PAGE') }}</h3>
        <FormLogin />
        <ul class="uk-list">
            <li>
                <router-link :to="{ name: 'account.forgot-password' }">
                    {{ $t('PASSWORD.FORGOT.QUESTION') }}
                </router-link>
            </li>
            <li v-if="useConfigStore().get('site.registration.require_email_verification')">
                <router-link :to="{ name: 'account.verification' }" data-test="gotoVerification">
                    {{ $t('ACCOUNT.VERIFICATION') }}
                </router-link>
            </li>
        </ul>
    </UFCardBoxHalf>
    <UFCardBoxHalf v-if="useConfigStore().get('site.registration.enabled')">
        <div class="uk-text-center">
            <p>{{ $t('REGISTRATION.QUESTION') }}</p>
            <router-link class="uk-button uk-button-default" :to="{ name: 'account.register' }">
                <font-awesome-icon :icon="['fas', 'user-plus']" /> {{ $t('REGISTER') }}
            </router-link>
        </div>
    </UFCardBoxHalf>
</template>
