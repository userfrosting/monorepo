import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useTranslator } from '@userfrosting/sprinkle-core/stores'
import { useConfigStore } from '../stores'
import { defineStore } from 'pinia'

/**
 * Page Meta Composable
 *
 * Handles the page meta data such as title, description, plus generate
 * breadcrumbs from the frontend router. The title, description and breadcrumbs
 * are updated automatically when the route changes.
 *
 * Available States : breadcrumbs, title, description
 */
export const usePageMeta = defineStore('pageMeta', () => {
    /**
     * Globally provided properties
     */
    const route = useRoute()
    const { translate } = useTranslator()

    /**
     * States
     *
     * - title: The current page title
     * - description: The current page description
     * - breadcrumbs: The current page breadcrumbs
     * - hideBreadcrumbs: Ask the component to hide the breadcrumbs on the page
     * - hideTitle: Ask the component to hide the title on the page
     */
    const title = ref<string>('')
    const description = ref<string>('')
    const breadcrumbs = ref<Breadcrumb[]>([])
    const hideBreadcrumbs = ref<boolean>(false)
    const hideTitle = ref<boolean>(false)

    /**
     * Actions - Refresh the breadcrumbs, title and description
     */
    function refresh() {
        // Reset default visibility attributes
        hideBreadcrumbs.value = false
        hideTitle.value = false

        // Get route trail
        const matchedRoutes = route.matched

        // Filter to remove routes without title and assign values as defined
        // in the breadcrumbs interface.
        const crumbs = matchedRoutes
            .filter(
                (routeItem) => routeItem.meta.title !== undefined && routeItem.meta.title !== ''
            )
            .map((routeItem) => {
                return {
                    label: routeItem.meta?.title || '',
                    to: routeItem.path
                }
            })

        // Add site title as first breadcrumb
        crumbs.unshift({
            label: siteTitle.value,
            to: '/'
        })

        // Replace ref with new values
        breadcrumbs.value = crumbs

        // Update Page Title & Description with current route
        title.value = route.meta.title || ''
        description.value = translate(route.meta.description || '')
    }

    // Update the document title
    function updatePageTitle() {
        document.title = pageFullTitle.value
    }

    // Update the document description in the HTML meta tag
    function updatePageDescription() {
        const descriptionElement = document.querySelector('head meta[name="description"]')
        descriptionElement?.setAttribute('content', description.value)
    }

    /**
     * Computed Properties - Getters
     *
     * - siteTitle: Return the site title from the config Store
     * - pageFullTitle: Return the full page title
     */
    const siteTitle = computed<string>(() => useConfigStore().get('site.title') || '')
    const pageFullTitle = computed<string>(() => {
        return title.value ? translate(title.value) + ' | ' + siteTitle.value : siteTitle.value
    })

    /**
     * Watchers - route, page title and description changes
     */
    watch(route, refresh, { immediate: true })
    watch(pageFullTitle, updatePageTitle, { immediate: true })
    watch(description, updatePageDescription, { immediate: true })

    /**
     * Returns the states and actions
     */
    return { breadcrumbs, title, description, hideBreadcrumbs, hideTitle }
})

interface Breadcrumb {
    label: string
    to: string
}
