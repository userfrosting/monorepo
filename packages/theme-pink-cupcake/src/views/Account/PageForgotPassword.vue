<script setup lang="ts">
import { ref } from 'vue'
import UIkit from 'uikit'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import { useForgotPasswordApi } from '@userfrosting/sprinkle-account/composables'
import type { ForgotPasswordSetPasswordRequest } from '@userfrosting/sprinkle-account/interfaces'
import FormEmailVerificationRequest from '../../components/Pages/Account/FormEmailVerificationRequest.vue'
import FormEmailVerificationValidation from '../../components/Pages/Account/FormEmailVerificationValidation.vue'
import FormForgotPasswordSet from '../../components/Pages/Account/FormForgotPasswordSet.vue'

/**
 * API - Use the forgotten password API.
 */
const { requestCode, setPassword, apiLoading, apiError } = useForgotPasswordApi()

/**
 * Steps Enumeration. Represents the different steps in the forgot password process.
 */
enum Steps {
    Request = 1,
    Validation,
    Set,
    Done
}

/**
 * Variables
 */
const currentStep = ref(Steps.Request)
const email = ref<string>('')
const code = ref<string>('')
const password = ref<string>('')
const passwordc = ref<string>('')

/**
 * Methods - Submit the form to the API and handle the response.
 */
async function sendRequestCode() {
    await requestCode(email.value)
        .then((message) => {
            UIkit.notification({
                message: message,
                status: 'success',
                pos: 'top-right',
                timeout: 4000
            })

            // Move to the validation step
            currentStep.value = Steps.Validation
        })
        .catch(() => {})
}

async function sendSetPassword() {
    const data: ForgotPasswordSetPasswordRequest = {
        email: email.value,
        code: code.value,
        password: password.value,
        passwordc: passwordc.value
    }
    await setPassword(data).then(() => {
        currentStep.value = Steps.Done
    })
}

function validateCode() {
    currentStep.value = Steps.Set
}

/**
 * Reset the form and go back to the first step
 */
function reset() {
    code.value = ''
    email.value = ''
    password.value = ''
    passwordc.value = ''
    apiError.value = null
    currentStep.value = Steps.Request
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
            <p class="uk-text-meta">{{ $t('PASSWORD.RESET.EMAIL') }}</p>
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
                @submit="validateCode()"
                @reset="reset()" />
        </div>

        <!-- Step 3 - Ask for the new password -->
        <div v-if="currentStep === Steps.Set">
            <h3 class="uk-card-title">
                {{ $t('STEP_X', { step: Steps.Set }) }} -
                {{ $t('PASSWORD.NEW') }}
            </h3>
            <p class="uk-text-meta">{{ $t('PASSWORD.NEW.EXPLAIN') }}</p>
            <UFAlert v-if="apiError" :alert="apiError" />
            <FormForgotPasswordSet
                v-model:password="password"
                v-model:passwordc="passwordc"
                :loading="!!apiLoading"
                @submit="sendSetPassword()" />
        </div>

        <!-- Step 4 - Success ! -->
        <div v-if="currentStep === Steps.Done">
            <h3 class="uk-card-title">
                {{ $t('STEP_X', { step: Steps.Done }) }} - {{ $t('SUCCESS') }} !
            </h3>
            <UFAlert
                :alert="{
                    style: Severity.Success,
                    title: $t('SUCCESS'),
                    description: $t('PASSWORD.RESET.SUCCESS')
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
                class="uk-button uk-button-default"
                :to="{ name: 'account.login' }"
                data-test="gotoLogin">
                <font-awesome-icon :icon="['fas', 'right-to-bracket']" /> {{ $t('LOGIN') }}
            </router-link>
        </div>
    </UFCardBoxLarge>
</template>
