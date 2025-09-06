<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps({
    length: {
        type: Number,
        default: 6,
        validator: (value: number) => value > 0
    }
})

const max = computed(() => Math.pow(10, props.length) - 1)
const code = defineModel()
</script>

<template>
    <div class="uk-margin">
        <div class="uk-inline uk-width-1-1">
            <span class="uk-form-icon">
                <font-awesome-icon :icon="['fas', 'shield-halved']" />
            </span>
            <input
                type="text"
                class="uk-input"
                v-model="code"
                :size="length"
                :max-size="length"
                min="0"
                :max="max"
                pattern="[0-9]*"
                inputmode="numeric"
                autocomplete="one-time-code"
                :placeholder="$t('ACCOUNT.VERIFICATION.CODE')"
                data-test="2FACode"
                aria-label="" />
        </div>
    </div>
</template>
