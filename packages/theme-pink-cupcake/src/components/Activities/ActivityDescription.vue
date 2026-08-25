<script setup lang="ts">
import { computed } from 'vue'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'


interface ActivityProperty {
    old: unknown
    new: unknown
}

interface ActivityLogEntry {
    description: string
    properties?: Record<string, ActivityProperty> | null
}

const props = defineProps<{
    activity: ActivityLogEntry
}>()

const { translate } = useTranslator()

function formatValue(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '-'
    }

    if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
        return translate(String(value))
    }

    return JSON.stringify(value) ?? '-'
}

const properties = computed(() => {
    return Object.entries(props.activity.properties ?? {})
})
</script>

<template>
    <div>
        <div v-html="activity.description" />
        <div v-for="[property, values] in properties" :key="property" class="uk-text-small">
            <span class="uk-text-muted">{{ $t(property.toUpperCase()) }}: </span>
            <strong>{{ formatValue(values.old) }}</strong>
            &rarr;
            <strong>{{ formatValue(values.new) }}</strong>
        </div>
    </div>
</template>
