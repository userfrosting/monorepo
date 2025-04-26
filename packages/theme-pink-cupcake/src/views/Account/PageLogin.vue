<script setup lang="ts">
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import FormLogin from '../../components/Pages/Account/FormLogin.vue'
</script>

<template>
    <UFCardBoxBig>
        <h3 class="uk-card-title">{{ $t('LOGIN') }}</h3>
        <FormLogin />
        <ul class="uk-list">
            <li>
                <a @click="$emit('gotoForgotPassword')" data-test="gotoForgotPassword">
                    {{ $t('PASSWORD.FORGET') }}
                </a>
            </li>
            <li v-if="useConfigStore().get('site.registration.require_email_verification')">
                <a @click="$emit('gotoVerification')" data-test="gotoVerification">
                    {{ $t('ACCOUNT.VERIFICATION') }}
                </a>
            </li>
        </ul>
        <hr />
        <div class="uk-text-center">
            <p>{{ $t('REGISTRATION.QUESTION') }}</p>
            <button
                class="uk-button uk-button-default"
                @click="$emit('gotoRegistration')"
                data-test="gotoRegistration">
                <font-awesome-icon :icon="['fas', 'user-plus']" /> {{ $t('REGISTER') }}
            </button>
        </div>
    </UFCardBoxBig>
</template>
