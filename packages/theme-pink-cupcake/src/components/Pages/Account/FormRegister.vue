<script setup lang="ts">
import axios from 'axios'
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEmailVerificationApi, useRegisterApi } from '@userfrosting/sprinkle-account/composables'
import { useConfigStore, useTranslator } from '@userfrosting/sprinkle-core/stores'
import FaCode from '../../Content/2FaCode.vue'

/**
 * API - Use the register & email verification API.
 */
const {
    submitRegistration,
    availableLocales,
    captchaUrl,
    formData,
    apiLoading,
    apiError,
    r$,
    r$username,
    passwordMinLength,
    passwordMaxLength
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
const code = ref<string>('')
const displayVerification = ref(false)

/**
 * Methods - Submit the form to the API and handle the response.
 */
const submitForm = async () => {
    // Make sure validation is up to date
    const isValid = await r$.$validate()
    if (!isValid.valid) return

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

const suggestUsername = async () => {
    const response = await useRegisterApi().suggestUsername()
    formData.value.user_name = response
}

/**
 * Computed - Generate the TOS agreement localized link
 */
const tos = computed(() => {
    const config = useConfigStore()
    const { translate } = useTranslator()
    return translate('TOS_AGREEMENT', {
        site_title: config.get('site.title')
    })
})

/**
 * Load the TOS from the axios API
 */
const tosContent = ref({ metadata: { title: '' }, content: '' })
onMounted(async () => {
    try {
        const response = await axios.get('/c/tos')
        tosContent.value = response.data
    } catch {
        tosContent.value = { metadata: { title: '' }, content: '' }
    }
})

// Load the Privacy Policy from the axios API
const privacyPolicyContent = ref({ metadata: { title: '' }, content: '' })
onMounted(async () => {
    try {
        const response = await axios.get('/c/privacy')
        privacyPolicyContent.value = response.data
    } catch {
        privacyPolicyContent.value = { metadata: { title: '' }, content: '' }
    }
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
                                :class="{ 'uk-form-danger': r$.first_name.$error }"
                                type="text"
                                :placeholder="$t('FIRST_NAME')"
                                aria-label="First Name"
                                id="first_name"
                                data-test="first_name"
                                autocomplete="off"
                                v-model="formData.first_name" />
                            <UFFormValidationError :errors="r$.$errors.first_name" />
                        </div>
                        <div class="uk-width-1-2">
                            <input
                                class="uk-input"
                                :class="{ 'uk-form-danger': r$.last_name.$error }"
                                type="text"
                                :placeholder="$t('LAST_NAME')"
                                aria-label="Last Name"
                                data-test="last_name"
                                autocomplete="off"
                                v-model="formData.last_name" />
                            <UFFormValidationError :errors="r$.$errors.last_name" />
                        </div>
                        <div class="uk-width-1-1">
                            <input
                                class="uk-input"
                                :class="{ 'uk-form-danger': r$.email.$error }"
                                type="email"
                                :placeholder="$t('EMAIL')"
                                aria-label="Email"
                                data-test="email"
                                autocomplete="off"
                                v-model="formData.email" />
                            <UFFormValidationError :errors="r$.$errors.email" />
                            <!-- TODO -->
                            <!-- {% if site.registration.require_email_verification %}{{ $t('EMAIL.VERIFICATION_REQUIRED') }}{% else %}{{ $t('EMAIL.YOUR') }}{% endif %} -->
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="form-stacked-text">{{
                        $t('USERNAME')
                    }}</label>
                    <div class="uk-form-controls uk-grid-small" uk-grid>
                        <div class="uk-width-2-3">
                            <input
                                class="uk-input"
                                :class="{
                                    'uk-form-danger': r$.user_name.$error || r$username.$error
                                }"
                                type="text"
                                :placeholder="$t('USERNAME.CHOOSE')"
                                aria-label="Username"
                                data-test="username"
                                autocomplete="off"
                                v-model="formData.user_name" />
                            <UFFormValidationError :errors="r$.$errors.user_name" />
                            <UFFormValidationError :errors="r$username.$errors.user_name" />
                        </div>
                        <div class="uk-width-1-3">
                            <a class="uk-button uk-button-default" @click="suggestUsername()">{{
                                $t('SUGGEST')
                            }}</a>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="form-stacked-text">{{
                        $t('PASSWORD')
                    }}</label>
                    <span class="uk-text-meta">
                        {{
                            $t('PASSWORD.BETWEEN', {
                                min: passwordMinLength,
                                max: passwordMaxLength
                            })
                        }}
                    </span>
                    <div class="uk-form-controls uk-grid-small" uk-grid>
                        <div class="uk-width-1-2">
                            <input
                                class="uk-input"
                                :class="{ 'uk-form-danger': r$.password.$error }"
                                type="password"
                                :placeholder="$t('PASSWORD')"
                                aria-label="Password"
                                data-test="password"
                                autocomplete="off"
                                v-model="formData.password" />
                            <UFFormValidationError :errors="r$.$errors.password" />
                        </div>
                        <div class="uk-width-1-2">
                            <input
                                class="uk-input"
                                :class="{ 'uk-form-danger': r$.passwordc.$error }"
                                type="password"
                                :placeholder="$t('PASSWORD.CONFIRM')"
                                aria-label="Confirm Password"
                                data-test="passwordc"
                                autocomplete="off"
                                v-model="formData.passwordc" />
                            <UFFormValidationError :errors="r$.$errors.passwordc" />
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="form-stacked-text">{{ $t('LOCALE') }}</label>
                    <span class="uk-text-meta">{{ $t('LOCALE.ACCOUNT') }}.</span>
                    <select
                        class="uk-select"
                        :class="{ 'uk-form-danger': r$.locale.$error }"
                        id="form-stacked-select"
                        data-test="locale"
                        v-model="formData.locale">
                        <option v-for="(value, key) in availableLocales()" :value="key" :key="key">
                            {{ value }}
                        </option>
                    </select>
                    <UFFormValidationError :errors="r$.$errors.locale" />
                </div>

                <!-- {% if site.registration.captcha %} -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="r-form-captcha">{{ $t('CAPTCHA') }}</label>
                    <div class="uk-form-controls uk-grid-small" uk-grid>
                        <div class="uk-width-2-3">
                            <input
                                class="uk-input"
                                :class="{ 'uk-form-danger': r$.captcha.$error }"
                                type="text"
                                :placeholder="$t('CAPTCHA.SPECIFY')"
                                aria-label="Captcha"
                                id="r-form-captcha"
                                data-test="captcha"
                                autocomplete="off"
                                v-model="formData.captcha" />
                            <UFFormValidationError :errors="r$.captcha.$errors" />
                        </div>
                        <div class="uk-width-1-3">
                            <img :src="captchaUrl()" id="captcha" data-target="#r-form-captcha" />
                        </div>
                    </div>
                </div>
                <!-- {% endif %} -->

                <!-- TOS modal -->
                <div class="uk-margin uk-text-center">
                    <p v-html="tos"></p>
                    <a href="#show-tos" class="uk-button uk-button-default" uk-toggle>
                        {{ $t('TOS') }}
                    </a>
                    <a href="#show-privacy" class="uk-button uk-button-default" uk-toggle>
                        {{ $t('PRIVACY_POLICY') }}
                    </a>
                    <UFModal class="uk-modal-container" id="show-tos" closable>
                        <template #header>{{ $t('TOS') }}</template>
                        <p uk-overflow-auto v-html="tosContent.content"></p>
                        <template #footer>
                            <button
                                class="uk-button uk-button-primary uk-width-1-1 uk-modal-close"
                                type="button">
                                Got it
                            </button>
                        </template>
                    </UFModal>
                    <UFModal class="uk-modal-container" id="show-privacy" closable>
                        <template #header>{{ $t('PRIVACY_POLICY') }}</template>
                        <p uk-overflow-auto v-html="privacyPolicyContent.content"></p>
                        <template #footer>
                            <button
                                class="uk-button uk-button-primary uk-width-1-1 uk-modal-close"
                                type="button">
                                Got it
                            </button>
                        </template>
                    </UFModal>
                </div>

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
