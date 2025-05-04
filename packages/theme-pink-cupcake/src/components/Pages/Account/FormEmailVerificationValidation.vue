<script setup lang="ts">
import FaCode from '../../Content/2FaCode.vue'

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
defineEmits(['submit', 'reset'])

/**
 * Model - Enabled the email value to be reactive and bind to the input.
 */
const code = defineModel()
</script>

<template>
    <form @submit.prevent="$emit('submit')" autocomplete="off" data-test="verificationForm">
        <fieldset class="uk-fieldset">
            <FaCode data-test="faCode" v-model="code" />
            <div class="uk-text-center">
                <button
                    class="uk-button uk-button-primary"
                    :disabled="props.loading"
                    type="submit"
                    data-test="submitVerification">
                    {{ $t('ACCOUNT.VERIFICATION.CODE.VERIFY') }}
                </button>
                <button
                    class="uk-button uk-button-default"
                    :disabled="props.loading"
                    data-test="tryAgain"
                    @click="$emit('reset')">
                    {{ $t('TRY_AGAIN') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
