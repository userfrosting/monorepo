<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useEmailVerificationApi, useRegisterApi } from '@userfrosting/sprinkle-account/composables'
import type { RegisterRequest } from '@userfrosting/sprinkle-account/interfaces'
import { useConfigStore, useTranslator } from '@userfrosting/sprinkle-core/stores'
import FaCode from '../../Content/2FaCode.vue'

/**
 * API - Use the register & email verification API.
 */
const {
    submitRegistration,
    defaultRegistrationForm,
    availableLocales,
    captchaUrl,
    apiLoading,
    apiError
} = useRegisterApi()

const {
    apiLoading: verificationApiLoading,
    apiError: verificationApiError,
    submitVerificationCode
} = useEmailVerificationApi()

/**
 * Variables - Copy the default form data to a reactive variable.
 */
const router = useRouter()
const formData = ref<RegisterRequest>(defaultRegistrationForm())
const code = ref<string>('')
const displayVerification = ref(false)

/**
 * TODO :
 *  1. Add Username Suggest
 *  2. Check username availability
 *  3. Add password strength, other config
 */

/**
 * Methods - Submit the form to the API and handle the response.
 */
const submitForm = async () => {
    await submitRegistration(formData.value)
        .then(() => {
            // Switch to the verification form on success
            // TODO : Only if email verification is required?
            displayVerification.value = true
        })
        .catch(() => {})
}

async function sendVerification() {
    await submitVerificationCode(formData.value.email, code.value).then(() => {
        // Redirect to the login page on success
        router.push({ name: 'account.login' })
    })
}

/**
 * Computed - Generate the TOS agreement localized link
 */
const tos = computed(() => {
    const config = useConfigStore()
    const { translate } = useTranslator()
    return translate('TOS_AGREEMENT', {
        site_title: config.get('site.title'),
        link_attributes: '' // TODO
    })
})
</script>

<template>
    <div v-if="!displayVerification">
        <form v-on:submit.prevent="submitForm()">
            <fieldset class="uk-fieldset uk-form-stacked">
                <UFAlert data-test="error" v-if="apiError" :alert="apiError" />

                <div class="uk-margin">
                    <label class="uk-form-label" for="first_name">{{ $t('NAME_AND_EMAIL') }}</label>
                    <div class="uk-form-controls uk-grid-small" uk-grid>
                        <div class="uk-width-1-2">
                            <input
                                class="uk-input"
                                type="text"
                                :placeholder="$t('FIRST_NAME')"
                                aria-label="First Name"
                                id="first_name"
                                data-test="first_name"
                                v-model="formData.first_name" />
                        </div>
                        <div class="uk-width-1-2">
                            <input
                                class="uk-input"
                                type="text"
                                :placeholder="$t('LAST_NAME')"
                                aria-label="Last Name"
                                data-test="last_name"
                                v-model="formData.last_name" />
                        </div>
                        <div class="uk-width-1-1">
                            <input
                                class="uk-input"
                                type="email"
                                :placeholder="$t('EMAIL')"
                                aria-label="Email"
                                data-test="email"
                                v-model="formData.email" />
                            <!-- TODO -->
                            <!-- {% if site.registration.require_email_verification %}{{ $t('EMAIL.VERIFICATION_REQUIRED') }}{% else %}{{ $t('EMAIL.YOUR') }}{% endif %} -->
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="form-stacked-text">{{
                        $t('USERNAME')
                    }}</label>
                    <!-- TODO -->
                    <!-- <span class="pull-right"><a href="#" id="form-register-username-suggest">[{{translate('SUGGEST')}}]</a></span> -->
                    <input
                        class="uk-input"
                        type="text"
                        :placeholder="$t('USERNAME.CHOOSE')"
                        aria-label="Username"
                        data-test="username"
                        v-model="formData.user_name" />
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="form-stacked-text">{{
                        $t('PASSWORD')
                    }}</label>
                    <div class="uk-form-controls uk-grid-small" uk-grid>
                        <div class="uk-width-1-2">
                            <input
                                class="uk-input"
                                type="password"
                                :placeholder="$t('PASSWORD')"
                                aria-label="Password"
                                data-test="password"
                                v-model="formData.password" />
                        </div>
                        <div class="uk-width-1-2">
                            <input
                                class="uk-input"
                                type="password"
                                :placeholder="$t('PASSWORD.CONFIRM')"
                                aria-label="Confirm Password"
                                data-test="passwordc"
                                v-model="formData.passwordc" />
                        </div>
                        <!-- TODO -->
                        <!-- {{translate('PASSWORD.BETWEEN', {min: site.password.length.min, max: site.password.length.max})}} -->
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="form-stacked-text">{{ $t('LOCALE') }}</label>
                    <span class="uk-text-meta">{{ $t('LOCALE.ACCOUNT') }}.</span>
                    <select
                        class="uk-select"
                        id="form-stacked-select"
                        data-test="locale"
                        v-model="formData.locale">
                        <option v-for="(value, key) in availableLocales()" :value="key" :key="key">
                            {{ value }}
                        </option>
                    </select>
                </div>

                <!-- {% if site.registration.captcha %} -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="r-form-captcha">{{ $t('CAPTCHA') }}</label>
                    <div class="uk-form-controls uk-grid-small" uk-grid>
                        <div class="uk-width-2-3">
                            <input
                                class="uk-input"
                                type="text"
                                :placeholder="$t('CAPTCHA.SPECIFY')"
                                aria-label="Captcha"
                                id="r-form-captcha"
                                data-test="captcha"
                                v-model="formData.captcha" />
                        </div>
                        <div class="uk-width-1-3">
                            <img :src="captchaUrl()" id="captcha" data-target="#r-form-captcha" />
                        </div>
                    </div>
                </div>
                <!-- {% endif %} -->

                <!-- TODO Add TOS in modal -->
                <p v-html="tos"></p>

                <div class="uk-text-center">
                    <button
                        class="uk-button uk-button-primary"
                        :disabled="apiLoading ? true : false"
                        data-test="submit">
                        {{ $t('REGISTER_ME') }}
                    </button>
                </div>
            </fieldset>
        </form>
    </div>

    <!-- Verification Form-->
    <form
        v-on:submit.prevent="sendVerification()"
        v-if="displayVerification"
        autocomplete="off"
        data-test="verificationForm">
        <h3 class="uk-card-title">{{ $t('ACCOUNT.VERIFICATION.CODE.ENTER') }}</h3>
        <p class="uk-text-meta">{{ $t('ACCOUNT.VERIFICATION.CODE.EXPLAIN') }}</p>
        <UFAlert
            data-test="verificationError"
            v-if="verificationApiError"
            :alert="verificationApiError" />
        <fieldset class="uk-fieldset">
            <FaCode data-test="faCode" v-model="code" />
            <div class="uk-text-center">
                <button
                    class="uk-button uk-button-primary"
                    :disabled="verificationApiLoading ? true : false"
                    data-test="submitVerification"
                    @click="sendVerification()">
                    {{ $t('ACCOUNT.VERIFICATION.CODE.VERIFY') }}
                </button>
                <button
                    class="uk-button uk-button-default"
                    :disabled="verificationApiLoading ? true : false"
                    data-test="tryAgain"
                    @click="router.push({ name: 'account.verification' })">
                    {{ $t('TRY_AGAIN') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
