<script setup lang="ts">
import { provide, ref } from 'vue'
import { useSprunjer } from '@userfrosting/sprinkle-core/composables'
import SprunjePaginator from './SprunjePaginator.vue'
import SprunjeSearch from './SprunjeSearch.vue'
import SprunjeFilters from './SprunjeFilters.vue'
import SprunjeDownload from './SprunjeDownload.vue'

/**
 * Props
 */
const {
    dataUrl,
    hidePagination = false,
    hideFilters = false,
    hideDownload = false,
    defaultSorts = {},
    defaultFilters = {},
    defaultSize = 10,
    defaultPage = 0,
    searchColumn = null
} = defineProps<{
    dataUrl: string
    hidePagination?: boolean
    hideFilters?: boolean
    hideDownload?: boolean
    defaultSorts?: { [key: string]: string }
    defaultFilters?: { [key: string]: string }
    defaultSize?: number | 'all'
    defaultPage?: number
    searchColumn?: string
}>()

/**
 * Component state
 */
const filterPanelOpen = ref(false)

/**
 * Sprunjer
 */
const sprunjer = useSprunjer(() => dataUrl, defaultSorts, defaultFilters, defaultSize, defaultPage)
const { rows } = sprunjer

/**
 * Provide sprunjer to children components
 */
provide('sprunjer', sprunjer)
</script>

<template>
    <div uk-grid class="uk-child-width-1-2">
        <div class="uk-text-left">
            <slot name="actions" :sprunjer="sprunjer"></slot>
        </div>
        <div class="uk-text-right">
            <slot name="filters" :sprunjer="sprunjer"></slot>
            <SprunjeSearch v-if="searchColumn" :column="searchColumn" />
            <a
                class="uk-button uk-button-default uk-button-small"
                @click="filterPanelOpen = !filterPanelOpen"
                v-if="!hideFilters">
                <font-awesome-icon icon="filter" />
            </a>
            <SprunjeDownload v-if="!hideDownload" />
        </div>
    </div>
    <div uk-grid class="uk-grid-small uk-grid-divider">
        <div class="uk-width-expand">
            <table class="uk-table uk-table-striped uk-table-small">
                <thead>
                    <tr>
                        <slot name="header" :sprunjer="sprunjer"></slot>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.id">
                        <slot name="body" :row="row" :sprunjer="sprunjer"></slot>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="filterPanelOpen === true" class="uk-width-1-4">
            <SprunjeFilters />
            <slot name="filterPanel" :sprunjer="sprunjer"></slot>
        </div>
    </div>
    <slot v-if="!hidePagination" name="paginator" :sprunjer="sprunjer">
        <SprunjePaginator />
    </slot>
</template>
