<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = withDefaults(
    defineProps<{
        to?: string | { name: string }
        label?: string
    }>(),
    {
        to: '',
        label: ''
    }
)

const isExternalLink = computed(() => {
    return typeof props.to === 'string' && props.to.startsWith('http')
})
</script>

<template>
    <li v-if="isExternalLink" v-bind="$attrs">
        <a
            class="uk-button uk-button-default uk-button-small uk-text-center"
            :href="to.toString()"
            target="_blank">
            <slot>{{ label }}</slot>
        </a>
    </li>
    <li v-else>
        <RouterLink :to="to" class="uk-button uk-button-default uk-button-small uk-text-center">
            <slot>{{ label }}</slot>
        </RouterLink>
    </li>
</template>
