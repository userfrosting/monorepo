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
const { rows, loading } = sprunjer

/**
 * Provide sprunjer to children components
 */
provide('sprunjer', sprunjer)
</script>

<template>
    <div uk-grid>
        <div class="uk-text-left uk-width-auto@s">
            <slot name="actions" :sprunjer="sprunjer"></slot>
        </div>
        <div
            class="uk-text-right uk-width-expand@s uk-flex uk-flex-right uk-flex-middle uk-flex-column uk-flex-row@s">
            <slot name="filters" :sprunjer="sprunjer"></slot>
            <SprunjeSearch v-if="searchColumn" :column="searchColumn" />
            <a
                class="uk-button uk-button-default uk-button-small uk-width-1-1 uk-width-auto@s uk-margin-small-bottom"
                @click="filterPanelOpen = !filterPanelOpen"
                v-if="!hideFilters">
                <font-awesome-icon icon="filter" />
            </a>
            <SprunjeDownload v-if="!hideDownload" />
        </div>
    </div>
    <div uk-grid class="uk-grid-small uk-grid-divider">
        <div v-if="filterPanelOpen === true" class="uk-width-1-4@m uk-flex-last@m">
            <SprunjeFilters />
            <slot name="filterPanel" :sprunjer="sprunjer"></slot>
        </div>
        <div v-if="loading" class="uk-width-1-1 uk-text-center">
            <span uk-spinner="ratio: 2"></span>
        </div>
        <div v-if="!loading && rows.length === 0" class="uk-width-1-1 uk-text-center">
            <p class="uk-text-muted">{{ $t('SPRUNJE.NO_RESULTS') }}</p>
        </div>
        <div v-else class="uk-width-expand uk-overflow-auto">
            <table class="uk-table uk-table-striped uk-table-small uk-table-responsive">
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
    </div>
    <slot v-if="!hidePagination" name="paginator" :sprunjer="sprunjer">
        <SprunjePaginator />
    </slot>
</template>
