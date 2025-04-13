<script setup lang="ts">
import { computed } from 'vue'
import { usePageMeta } from '@userfrosting/sprinkle-core/stores'
const page = usePageMeta()

const shouldShowHeader = computed(() => {
    return page.title || page.description || page.breadcrumbs.length > 1
})

const shouldShowTitle = computed(() => {
    return page.title && page.hideTitle === false
})

const shouldShowDescription = computed(() => {
    return page.description && page.hideTitle === false
})

const shouldShowBreadcrumbs = computed(() => {
    return page.breadcrumbs.length > 1 && page.hideBreadcrumbs === false
})
</script>

<template>
    <div class="uf-page-header" v-if="shouldShowHeader">
        <div uk-grid>
            <div class="uk-width-expand">
                <h3 v-if="shouldShowTitle" class="uk-margin-remove">
                    {{ $t(page.title) }}
                </h3>
                <p v-if="shouldShowDescription" class="uk-text-meta uk-margin-remove">
                    {{ $t(page.description) }}
                </p>
            </div>
            <div>
                <nav aria-label="Breadcrumb" v-if="shouldShowBreadcrumbs">
                    <ul class="uk-breadcrumb">
                        <li v-for="(crumb, index) in page.breadcrumbs" :key="index">
                            <span aria-current="page" v-if="index + 1 === page.breadcrumbs.length">
                                {{ $t(page.title) }}
                            </span>
                            <router-link v-else :to="crumb.to">
                                <font-awesome-icon icon="house" fixed-width v-if="index === 0" />
                                {{ $t(crumb.label) }}
                            </router-link>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>
