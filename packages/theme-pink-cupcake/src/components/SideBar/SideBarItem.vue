<script setup lang="ts">
/**
 * Allow external + internal links in same component, plus add active class to
 * li instead of a element
 * @see https://router.vuejs.org/guide/advanced/extending-router-link
 */
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = withDefaults(
    defineProps<{
        to?: string | { name: string }
        label?: string
        faIcon?: string
        icon?: string
    }>(),
    {
        to: '',
        label: '',
        faIcon: '',
        icon: ''
    }
)

const isExternalLink = computed(() => {
    return props.to === '' || (typeof props.to === 'string' && props.to.startsWith('http'))
})
</script>

<template>
    <li v-if="isExternalLink">
        <a :href="to.toString()" target="_blank">
            <span
                v-if="icon"
                :data-uk-icon="icon"
                class="uk-margin-small-right"
                data-test="icon"></span>
            <font-awesome-icon
                v-if="faIcon"
                class="uk-margin-small-right"
                :icon="faIcon"
                fixed-width
                data-test="faIcon" />
            <slot>{{ label }}</slot>
        </a>
    </li>
    <RouterLink v-else v-bind="$props" custom v-slot="{ isActive, href, navigate }">
        <li :class="{ 'uk-active': isActive }">
            <a v-bind="$attrs" :href="href" @click="navigate">
                <span
                    v-if="icon"
                    :data-uk-icon="icon"
                    class="uk-margin-small-right"
                    data-test="icon"></span>
                <font-awesome-icon
                    v-if="faIcon"
                    class="uk-margin-small-right"
                    :icon="faIcon"
                    fixed-width
                    data-test="faIcon" />
                <slot>{{ label }}</slot>
            </a>
        </li>
    </RouterLink>
</template>
