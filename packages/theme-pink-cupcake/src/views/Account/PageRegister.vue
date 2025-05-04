<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useConfigStore, usePageMeta } from '@userfrosting/sprinkle-core/stores'
import FormRegister from '../../components/Pages/Account/FormRegister.vue'
usePageMeta().hideTitle = true
const router = useRouter()

/**
 * Redirect if registration is not enabled.
 */
if (useConfigStore().get('site.registration.enabled') === false) {
    router.push({ name: 'account.login' })
}
</script>

<template>
    <UFCardBoxLarge>
        <h3 class="uk-card-title">{{ $t('REGISTER') }}</h3>
        <FormRegister />
    </UFCardBoxLarge>
    <UFCardBoxLarge>
        <div class="uk-text-center">
            <p>{{ $t('ACCOUNT.HAVE_ONE') }}</p>
            <router-link
                class="uk-button uk-button-default"
                :to="{ name: 'account.login' }"
                data-test="gotoLogin">
                <font-awesome-icon :icon="['fas', 'right-to-bracket']" /> {{ $t('LOGIN') }}
            </router-link>
        </div>
    </UFCardBoxLarge>
</template>
