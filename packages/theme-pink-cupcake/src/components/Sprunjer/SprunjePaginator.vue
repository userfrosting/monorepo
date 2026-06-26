<script setup lang="ts">
import { inject } from 'vue'
import type { Sprunjer } from '@userfrosting/sprinkle-core/interfaces'

interface Props {
    rowsPerPageOptions?: number[]
}

withDefaults(defineProps<Props>(), {
    rowsPerPageOptions: () => [5, 10, 20, 50]
})

const sprunjer = inject('sprunjer') as Sprunjer
const { size, page, totalPages, countFiltered, first, last } = sprunjer

function goToPage(goToPage: number) {
    page.value = goToPage
}
</script>

<template>
    <div class="uk-grid-medium uk-flex uk-flex-middle" uk-grid>
        <div class="uk-width-auto@s uk-text-center uk-text-meta">
            <span>{{
                $t('PAGINATION.OUTPUT', {
                    first: first,
                    last: last,
                    count: countFiltered
                })
            }}</span>
        </div>
        <div class="uk-width-expand@s uk-text-center uk-text-meta">
            {{ $t('PAGINATION.GOTO') }} :
            <select
                class="uk-select uk-form-small uk-form-width-xsmall"
                aria-label="Select page"
                v-model="page">
                <option v-for="n in totalPages + 1" :key="n" :value="n - 1">{{ n }}</option>
            </select>
            <select
                class="uk-select uk-form-small uk-form-width-small"
                aria-label="Select per page"
                v-model="size">
                <option v-for="option in rowsPerPageOptions" :key="option" :value="option">
                    {{ $t('PAGINATION.PER_PAGE', { count: option }) }}
                </option>
            </select>
        </div>
        <div class="uk-width-auto@s">
            <nav aria-label="Pagination">
                <ul class="uk-pagination uk-flex-center uk-margin-remove" uk-margin>
                    <li>
                        <a
                            @click="goToPage(0)"
                            class="uk-icon-link"
                            :class="{ 'uk-disabled': page === 0 }">
                            <font-awesome-icon icon="angles-left" fixed-width />
                        </a>
                    </li>
                    <li>
                        <a
                            @click="goToPage(page - 1)"
                            class="uk-icon-link"
                            :class="{ 'uk-disabled': page === 0 }">
                            <font-awesome-icon icon="angle-left" fixed-width />
                        </a>
                    </li>
                    <li>
                        {{
                            $t('PAGINATION.PAGE_X_OF_Y', {
                                current: page + 1,
                                last: totalPages + 1
                            })
                        }}
                    </li>
                    <li>
                        <a
                            @click="goToPage(page + 1)"
                            class="uk-icon-link"
                            :class="{ 'uk-disabled': page === totalPages }">
                            <font-awesome-icon icon="angle-right" fixed-width />
                        </a>
                    </li>
                    <li>
                        <a
                            @click="goToPage(totalPages)"
                            class="uk-icon-link"
                            :class="{ 'uk-disabled': page === totalPages }">
                            <font-awesome-icon icon="angles-right" fixed-width />
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>
