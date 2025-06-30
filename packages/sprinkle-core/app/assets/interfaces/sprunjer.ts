/**
 * Sprunjer Interface
 *
 * Represents the interface for the Sprunjer composable.
 */
import type { Ref, ComputedRef } from 'vue'
import type { ApiErrorResponse, AssociativeArray } from '.'

export interface Sprunjer {
    dataUrl: string | (() => string)
    size: Ref<number>
    page: Ref<number>
    sorts: Ref<AssociativeArray>
    filters: Ref<AssociativeArray>
    data: Ref<SprunjerData>
    fetch: () => void
    loading: Ref<boolean>
    error: Ref<ApiErrorResponse | null>
    totalPages: ComputedRef<number>
    downloadCsv: () => void
    countFiltered: ComputedRef<number>
    count: ComputedRef<number>
    rows: ComputedRef<any[]>
    first: ComputedRef<number>
    last: ComputedRef<number>
    toggleSort: (column: string) => void
}

/**
 * Sprunjer Data. Represents the data that is returned from any Sprunjer
 * Composable. It is different than SprunjerResponse, as the response if what
 * the API return, Data is what Vue provides. Both are similar, but Data doesn't
 * have optional values.
 *
 * N.B.: "rows" uses a generic array. It can contain any object, and should
 * actually be can be extended for each Sprunjer
 */
export interface SprunjerData {
    count: number
    count_filtered: number
    rows: any[]
    listable: SprunjerListable
    sortable: string[]
    filterable: string[]
}

/**
 * Sprunjer Listable. Represents a listable for a Sprunjer.
 */
export interface SprunjerListable {
    [key: string]: SprunjerListableOption[]
}

/**
 * Sprunjer Listable Option. Represents a listable option for a Sprunjer.
 */
export interface SprunjerListableOption {
    value: string
    text: string
}
