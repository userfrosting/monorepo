/**
 * Sprunjer Composable
 *
 * A composable function that fetches data from a Sprunjer API and provides
 * all necessary function like pagination, sorting and filtering.
 *
 * Pass the URL of the Sprunjer API to the function, and it will fetch the data.
 * A watcher will refetch the data whenever any parameters change.
 *
 * Params:
 * @param {String} dataUrl - The URL of the Sprunjer API
 * @param {Object} defaultSorts - An object of default sorts
 * @param {Object} defaultFilters - An object of default filters
 * @param {Number} defaultSize - The default number of items per page
 * @param {Number} defaultPage - The default page number
 *
 * Exports:
 * - size: The number of items per page
 * - page: The current page number
 * - sorts: An object of sorts
 * - filters: An object of filters
 * - data: The raw data from the API
 * - fetch: A function to fetch the data
 * - loading: A boolean indicating if the data is loading
 * - totalPages: The total number of pages
 * - downloadCsv: A function to download the data as a CSV file
 * - countFiltered: The total number of items after filtering
 * - count: The total number of items
 * - rows: The rows of data
 * - first: The index of the first item on the current page
 * - last: The index of the last item on the current page
 * - toggleSort: A function to toggle the sort order of a column
 */
import { ref, toValue, watchEffect, computed } from 'vue'
import axios from 'axios'
import type { AssociativeArray, Sprunjer, SprunjerData, SprunjerResponse } from '../interfaces'

export const useSprunjer = (
    dataUrl: string | (() => string),
    defaultSorts: AssociativeArray = {},
    defaultFilters: AssociativeArray = {},
    defaultSize: number = 10,
    defaultPage: number = 0
): Sprunjer => {
    // Sprunje parameters
    const size = ref<number>(defaultSize)
    const page = ref<number>(defaultPage)
    const sorts = ref<AssociativeArray>(defaultSorts)
    const filters = ref<AssociativeArray>(defaultFilters)

    // Raw data - Init with default data
    const data = ref<SprunjerData>({
        count: 0,
        count_filtered: 0,
        rows: [],
        listable: {},
        sortable: [],
        filterable: []
    })

    // State
    const loading = ref<boolean>(false)

    /**
     * Api fetch function
     */
    async function fetch() {
        loading.value = true
        axios
            .get<SprunjerResponse>(toValue(dataUrl), {
                params: {
                    size: size.value,
                    page: page.value,
                    sorts: sorts.value,
                    filters: filters.value
                }
            })
            .then((response) => {
                // Assign the response data to the Sprunje Data.
                // Note both object can't be assigned directly, as the response
                // object is not a SprunjeData object.
                data.value.count = response.data.count
                data.value.count_filtered = response.data.count_filtered
                data.value.rows = response.data.rows
                data.value.listable = response.data.listable ?? {}
                data.value.sortable = response.data.sortable ?? []
                data.value.filterable = response.data.filterable ?? []
            })
            .catch((err) => {
                // TODO : User toast alert, or export alert
                console.error(err)
            })
            .finally(() => {
                loading.value = false
            })
    }

    /**
     * Computed properties
     */
    const totalPages = computed(() => {
        // N.B.: Sprunjer page starts at 0, not 1
        // Make sure page is never negative
        return Math.max(Math.ceil((data.value.count_filtered ?? 0) / size.value) - 1, 0)
    })

    const count = computed(() => {
        return data.value.count ?? 0
    })

    const first = computed(() => {
        return Math.min(page.value * size.value + 1, data.value.count ?? 0)
    })

    const last = computed(() => {
        return Math.min((page.value + 1) * size.value, data.value.count_filtered ?? 0)
    })

    const countFiltered = computed(() => {
        return data.value.count_filtered ?? 0
    })

    const rows = computed(() => {
        return data.value.rows ?? []
    })

    /**
     * Download the data as a CSV file
     */
    function downloadCsv() {
        console.log('Not yet implemented')
    }

    /**
     * Apply sorting to a column, cycling from the previous sort order.
     * Order goes : asc -> desc -> null -> asc
     * Used to toggle the sort order of a column when the column header is clicked
     * @param column The column to sort
     */
    function toggleSort(column: string) {
        let newOrder: string | null
        if (sorts.value[column] === 'asc') {
            newOrder = 'desc'
        } else if (sorts.value[column] === 'desc') {
            newOrder = null
        } else {
            newOrder = 'asc'
        }

        sorts.value[column] = newOrder
    }

    /**
     * Automatically fetch the data when any parameters change
     */
    watchEffect(() => {
        fetch()
    })

    /**
     * Export the functions and data
     */
    return {
        dataUrl,
        size,
        page,
        sorts,
        filters,
        data,
        fetch,
        loading,
        downloadCsv,
        totalPages,
        countFiltered,
        count,
        rows,
        first,
        last,
        toggleSort
    }
}
