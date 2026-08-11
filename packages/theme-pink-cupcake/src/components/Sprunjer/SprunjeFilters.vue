<script setup lang="ts">
import { inject, computed } from 'vue'
import type { Sprunjer, SprunjerListable } from '@userfrosting/sprinkle-core/interfaces'

const sprunjer = inject('sprunjer') as Sprunjer
const { data, filters } = sprunjer

const filterable = computed((): string[] => {
    return data.value.filterable
})

const listable = computed((): SprunjerListable => {
    return data.value.listable
})

const resetFilters = () => {
    filters.value = {}
}

const isListable = (column: string) => {
    return column in listable.value
}

const listableOptions = (column: string) => {
    return listable.value[column]
}
</script>

<template>
    <h3 class="uk-heading-divider">{{ $t('SPRUNJE.FILTERS') }}</h3>
    <dl class="uk-description-list" v-for="(column, index) in filterable" :key="index">
        <dt>{{ $t(column === 'label' ? 'LABEL' : column) }}</dt>
        <dd>
            <select v-if="isListable(column)" v-model="filters[column]" class="uk-select">
                <option value=""></option>
                <option
                    v-for="option in listableOptions(column)"
                    :key="option.value"
                    :value="option.value">
                    {{ option.text }}
                </option>
            </select>
            <input
                v-else
                class="uk-input"
                type="text"
                :placeholder="column"
                v-model="filters[column]" />
        </dd>
    </dl>
    <button class="uk-button uk-button-default uk-button-small" @click="resetFilters">
        <font-awesome-icon icon="xmark" fixed-width /> {{ $t('SPRUNJE.FILTER_CLEAR') }}
    </button>
</template>
