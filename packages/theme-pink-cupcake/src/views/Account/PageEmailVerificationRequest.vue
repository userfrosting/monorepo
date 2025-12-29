<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import { useEmailVerificationApi } from '@userfrosting/sprinkle-account/composables'
import FormEmailVerificationRequest from '../../components/Pages/Account/FormEmailVerificationRequest.vue'
import FormEmailVerificationValidation from '../../components/Pages/Account/FormEmailVerificationValidation.vue'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'

/**
 * API - Use the user verification API.
 */
const { apiLoading, apiError, submitVerificationCode, requestVerificationCode } =
    useEmailVerificationApi()

/**
 * Steps Enumeration. Represents the different steps in the forgot password process.
 */
enum Steps {
    Request = 1,
    Validation,
    Done
}

/**
 * Variables
 */
const router = useRouter()
const currentStep = ref(Steps.Request)
const email = ref<string>('')
const code = ref<string>('')

/**
 * Methods - Submit the form to the API and handle the response.
 */
async function sendRequestCode() {
    await requestVerificationCode(email.value).then(() => {
        // Move to the validation step
        currentStep.value = Steps.Validation
    })
}

async function sendVerification() {
    await submitVerificationCode(email.value, code.value).then(() => {
        currentStep.value = Steps.Done
    })
}

/**
 * Reset the form and go back to the first step
 */
function reset() {
    code.value = ''
    email.value = ''
    apiError.value = null
    currentStep.value = Steps.Request
}

/**
 * Redirect if email verification is not enabled.
 */
if (useConfigStore().get('site.registration.require_email_verification') === false) {
    router.push({ name: 'account.login' })
}
</script>

<template>
    <UFCardBoxLarge>
        <!-- Step 1 - Ask for email -->
        <div v-if="currentStep === Steps.Request">
            <h3 class="uk-card-title">
                {{ $t('STEP_X', { step: Steps.Request }) }} -
                {{ $t('ACCOUNT.VERIFICATION.CODE.IDENTIFY') }}
            </h3>
            <p class="uk-text-meta">{{ $t('ACCOUNT.VERIFICATION.EXPLAIN') }}</p>
            <UFAlert v-if="apiError" :alert="apiError" />
            <FormEmailVerificationRequest
                v-model="email"
                :loading="!!apiLoading"
                @submit="sendRequestCode" />
        </div>

        <!-- Step 2 - Ask for the validation code -->
        <div v-if="currentStep === Steps.Validation">
            <h3 class="uk-card-title">
                {{ $t('STEP_X', { step: Steps.Validation }) }} -
                {{ $t('ACCOUNT.VERIFICATION.CODE.ENTER') }}
            </h3>
            <p class="uk-text-meta">{{ $t('ACCOUNT.VERIFICATION.CODE.EXPLAIN') }}</p>
            <UFAlert v-if="apiError" :alert="apiError" />
            <FormEmailVerificationValidation
                v-model="code"
                :loading="!!apiLoading"
                @submit="sendVerification()"
                @reset="reset()" />
        </div>

        <!-- Step 3 - Success ! -->
        <div v-if="currentStep === Steps.Done">
            <h3 class="uk-card-title">
                {{ $t('STEP_X', { step: Steps.Done }) }} - {{ $t('SUCCESS') }} !
            </h3>
            <UFAlert
                :alert="{
                    style: Severity.Success,
                    title: $t('SUCCESS'),
                    description: $t('ACCOUNT.VERIFICATION.COMPLETE')
                }" />
            <div class="uk-text-center">
                <router-link
                    class="uk-button uk-button-primary"
                    :to="{ name: 'account.login' }"
                    data-test="gotoLogin">
                    <font-awesome-icon :icon="['fas', 'right-to-bracket']" /> {{ $t('LOGIN') }}
                </router-link>
            </div>
        </div>
    </UFCardBoxLarge>

    <!-- Link back to the login form -->
    <UFCardBoxLarge v-if="currentStep !== Steps.Done">
        <div class="uk-text-center">
            <p>{{ $t('ACCOUNT.HAVE_ONE') }}</p>
            <router-link
                class="uk-button uk-button-default uk-width-1-3@s"
                :to="{ name: 'account.login' }"
                data-test="gotoLogin">
                <font-awesome-icon :icon="['fas', 'right-to-bracket']" /> {{ $t('LOGIN') }}
            </router-link>
        </div>
    </UFCardBoxLarge>
</template>
