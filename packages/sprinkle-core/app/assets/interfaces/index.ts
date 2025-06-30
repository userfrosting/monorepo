/**
 * Interface for custom routes meta fields.
 *
 * Meta Fields Added :
 * - title: string - Page title
 * - description: string - Page description
 *
 * Theses fields are used to set the document title and description, as well as
 * for breadcrumbs generation.
 *
 * @see https://router.vuejs.org/guide/advanced/meta.html#TypeScript
 */
import 'vue-router'

declare module 'vue-router' {
    interface RouteMeta {
        title?: string
        description?: string
    }
}

export type { AlertInterface } from './alerts'
export type { AssociativeArray } from './common'
export { Severity } from './severity'
export type { Sprunjer, SprunjerData, SprunjerListable, SprunjerListableOption } from './sprunjer'
export type { SprunjerRequest, SprunjerResponse } from './sprunjerApi'
export type { DictionaryResponse, DictionaryEntries, DictionaryConfig } from './DictionaryApi'

// Misc
export type { ApiResponse, ApiErrorResponse } from './ApiResponse'
