/**
 * Api Interface - What the API expects and what it returns
 *
 * This interface is tied to the `DictionaryController` API, accessed at the
 * GET `/api/dictionary` endpoint.
 *
 * This api doesn't have a corresponding Request data interface.
 */
export interface DictionaryResponse {
    identifier: string
    config: DictionaryConfig
    dictionary: DictionaryEntries
}

export interface DictionaryEntries {
    [key: string]: string
}

export interface DictionaryConfig {
    name: string
    regional: string
    authors: string[]
    plural_rule: number
    tag: string
}
