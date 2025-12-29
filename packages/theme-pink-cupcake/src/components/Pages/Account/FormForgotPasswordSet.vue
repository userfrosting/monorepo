<script setup lang="ts">
/**
 * Props - Loading state for the form.
 */
const props = defineProps({
    loading: {
        type: Boolean,
        default: false
    }
})

/**
 * Emits - Define the events emitted by the component.
 */
defineEmits(['submit'])

/**
 * Model - Enabled the email value to be reactive and binded to the input.
 */
const password = defineModel('password')
const passwordc = defineModel('passwordc')
</script>

<template>
    <form @submit.prevent="$emit('submit')" autocomplete="off" data-test="newPasswordForm">
        <fieldset class="uk-fieldset">
            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{
                    $t('PASSWORD.NEW')
                }}</label>
                <div class="uk-inline uk-width-1-1">
                    <span class="uk-form-icon">
                        <font-awesome-icon :icon="['fas', 'lock']" />
                    </span>
                    <input
                        class="uk-input"
                        type="password"
                        :placeholder="$t('PASSWORD.NEW')"
                        aria-label="Password"
                        data-test="password"
                        v-model="password" />
                </div>
            </div>
            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">{{
                    $t('PASSWORD.CONFIRM_NEW')
                }}</label>
                <div class="uk-inline uk-width-1-1">
                    <span class="uk-form-icon">
                        <font-awesome-icon :icon="['fas', 'lock']" />
                    </span>
                    <input
                        class="uk-input"
                        type="password"
                        :placeholder="$t('PASSWORD.CONFIRM_NEW')"
                        aria-label="Confirm Password"
                        data-test="passwordc"
                        v-model="passwordc" />
                </div>
                <!-- TODO -->
                <!-- {{translate('PASSWORD.BETWEEN', {min: site.password.length.min, max: site.password.length.max})}} -->
            </div>

            <div class="uk-text-center">
                <button
                    class="uk-button uk-button-primary uk-width-1-3@s"
                    :disabled="props.loading"
                    type="submit"
                    data-test="submitNewPassword">
                    {{ $t('PASSWORD.NEW.SET') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
