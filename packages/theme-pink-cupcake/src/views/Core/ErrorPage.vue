<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { usePageMeta } from '@userfrosting/sprinkle-core/composables'
const page = usePageMeta()

onMounted(() => {
    page.hideTitle = true
    page.hideBreadcrumbs = true
})

const props = defineProps({
    errorCode: {
        type: String
    }
})

const faIcon = computed(() => {
    switch (props.errorCode) {
        case '401':
            return 'lock'
        case '403':
            return 'ban'
        case '404':
            return 'magnifying-glass'
        default:
            return 'triangle-exclamation'
    }
})

const title = computed(() => {
    switch (props.errorCode) {
        case '400':
        case '401':
        case '403':
        case '404':
        case '405':
        case '410':
            return 'ERROR.' + props.errorCode + '.TITLE'
        default:
            return 'ERROR.TITLE'
    }
})

const description = computed(() => {
    switch (props.errorCode) {
        case '400':
        case '401':
        case '403':
        case '404':
        case '405':
        case '410':
            return 'ERROR.' + props.errorCode + '.DESCRIPTION'
        default:
            return 'ERROR.DESCRIPTION'
    }
})
</script>

<template>
    <UFInfoBox to="/" :faIcon="faIcon" :value="$t(title)" :label="$t(description)" />
</template>
