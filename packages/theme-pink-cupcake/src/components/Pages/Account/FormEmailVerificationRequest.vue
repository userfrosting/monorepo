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
 * Model - Enabled the email value to be reactive and bind to the input.
 */
const email = defineModel()
</script>

<template>
    <form @submit.prevent="$emit('submit')" autocomplete="off">
        <fieldset class="uk-fieldset">
            <div class="uk-margin">
                <div class="uk-inline uk-width-1-1">
                    <span class="uk-form-icon">
                        <font-awesome-icon :icon="['fas', 'envelope']" />
                    </span>
                    <input
                        class="uk-input"
                        type="email"
                        :placeholder="$t('EMAIL')"
                        aria-label="Email"
                        data-test="email"
                        data-1p-ignore
                        data-lpignore="true"
                        data-protonpass-ignore="true"
                        v-model="email" />
                </div>
            </div>
            <div class="uk-text-center">
                <button
                    class="uk-button uk-button-primary"
                    type="submit"
                    :disabled="props.loading"
                    data-test="submit">
                    {{ $t('ACCOUNT.VERIFICATION.CODE.SEND') }}
                </button>
            </div>
        </fieldset>
    </form>
</template>
