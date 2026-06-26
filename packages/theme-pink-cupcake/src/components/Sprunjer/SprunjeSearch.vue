<script setup lang="ts">
import { inject, computed } from 'vue'
import type { Sprunjer } from '@userfrosting/sprinkle-core/interfaces'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'

const props = defineProps({
    column: {
        type: String,
        required: true
    },
    label: {
        type: String
    }
})

const sprunjer = inject('sprunjer') as Sprunjer
const { filters } = sprunjer

const searchLabel = computed(() => {
    const { translate } = useTranslator()
    return props.label ?? translate('SPRUNJE.SEARCH', { term: props.column })
})
</script>

<template>
    <div
        class="uk-search uk-search-default uk-width-1-1 uk-width-auto@s uk-margin-small-bottom"
        v-if="column">
        <input
            class="uk-search-input uk-form-small"
            type="search"
            :placeholder="searchLabel"
            aria-label="Search"
            v-model="filters[column]" />
        <span class="uk-search-icon-flip" uk-search-icon></span>
    </div>
</template>
