<script setup lang="ts">
import { ref } from 'vue'
import UIkit from 'uikit'
import { useRouter } from 'vue-router'
import { useEmailVerificationApi } from '@userfrosting/sprinkle-account/composables'
import type { ValidateCodeResponse } from '@userfrosting/sprinkle-account/interfaces'
import FaCode from '../../Content/2FaCode.vue'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'

/**
 * API - Use the user verification API.
 */
const { apiLoading, apiError, submitVerificationCode, resendVerification } =
    useEmailVerificationApi()

/**
 * Variables - Define the variables used in the component.
 */
const router = useRouter()
const email = ref<string>('')
const code = ref<string>('')
const displayVerification = ref(false)

/**
 * Methods - Submit the form to the API and handle the response.
 */
async function sendForm() {
    await resendVerification(email.value).then((message) => {
        UIkit.notification({
            message: message,
            status: 'success',
            pos: 'top-right',
            timeout: 4000
        })
        displayVerification.value = true
    })
}

async function sendVerification() {
    await submitVerificationCode(email.value, code.value).then((response: ValidateCodeResponse) => {
        UIkit.notification({
            message: response.message,
            status: 'success',
            pos: 'top-right',
            timeout: 4000
        })

        // Reset the form
        reset()

        // Redirect to the login page
        router.push({ name: 'account.login' })
    })
}

function reset() {
    code.value = ''
    email.value = ''
    displayVerification.value = false
    apiError.value = null
}

/**
 * Redirect if email verification is not enabled.
 */
if (useConfigStore().get('site.registration.require_email_verification') === false) {
    router.push({ name: 'account.login' })
}
</script>

<template>
    <!-- Email form -->
    <form @submit.prevent="sendForm()" v-if="!displayVerification" data-test="requestForm">
        <h3 class="uk-card-title">{{ $t('ACCOUNT.VERIFICATION') }}</h3>
        <p class="uk-text-meta">{{ $t('ACCOUNT.VERIFICATION.EXPLAIN') }}</p>
        <fieldset class="uk-fieldset">
            <UFAlert data-test="error" v-if="apiError" :alert="apiError" />
            <div class="uk-margin">
                <div class="uk-inline uk-width-1-1">
                    <span class="uk-form-icon" uk-icon="icon: mail"></span>
                    <input
                        class="uk-input"
                        type="email"
                        :placeholder="$t('EMAIL')"
                        aria-label="Email"
                        data-test="email"
                        :disabled="displayVerification"
                        v-model="email" />
                </div>
            </div>
            <div class="uk-text-center">
                <button
                    type="submit"
                    class="uk-button uk-button-primary"
                    :disabled="apiLoading ? true : false"
                    data-test="submitRequest">
                    {{ $t('ACCOUNT.VERIFICATION.SEND') }}
                </button>
            </div>
        </fieldset>
    </form>

    <!-- Verification Form-->
    <form
        v-on:submit.prevent="sendVerification()"
        v-if="displayVerification"
        autocomplete="off"
        data-test="verificationForm">
        <h3 class="uk-card-title">{{ $t('ACCOUNT.VERIFICATION.CODE.ENTER') }}</h3>
        <p class="uk-text-meta">{{ $t('ACCOUNT.VERIFICATION.CODE.EXPLAIN') }}</p>
        <UFAlert data-test="error" v-if="apiError" :alert="apiError" />
        <fieldset class="uk-fieldset">
            <FaCode data-test="faCode" v-model="code" />
            <div class="uk-text-center">
                <button
                    class="uk-button uk-button-primary"
                    :disabled="apiLoading ? true : false"
                    data-test="submitVerification"
                    @click="sendVerification()">
                    {{ $t('ACCOUNT.VERIFICATION.CODE.VERIFY') }}
                </button>
                <button
                    class="uk-button uk-button-default"
                    :disabled="apiLoading ? true : false"
                    data-test="tryAgain"
                    @click="reset()">
                    {{ $t('TRY_AGAIN') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
