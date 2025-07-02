import { ref, watchEffect } from 'vue'
import { useConfigStore } from '../stores'
import axios from 'axios'

/**
 * CSRF Protection Composable
 *
 * Automatically sets the CSRF token in the axios headers for all requests.
 * The CSRF token is read from the meta tags in the HTML document.
 * The CSRF token can updated when the server responds with a new token in the headers.
 *
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#axios
 */
export const useCsrf = () => {
    /**
     * Public constant for the CSRF token name and value, plus respective keys.
     */
    const key_name = ref(getNameKey())
    const key_value = ref(getValueKey())
    const name = ref(readMetaTag(key_name.value))
    const token = ref(readMetaTag(key_value.value))

    /**
     * Set the axios headers for CSRF protection
     */
    function setAxiosHeader() {
        axios.defaults.headers.post[key_name.value] = name.value
        axios.defaults.headers.post[key_value.value] = token.value
        axios.defaults.headers.put[key_name.value] = name.value
        axios.defaults.headers.put[key_value.value] = token.value
        axios.defaults.headers.delete[key_name.value] = name.value
        axios.defaults.headers.delete[key_value.value] = token.value
        axios.defaults.headers.patch[key_name.value] = name.value
        axios.defaults.headers.patch[key_value.value] = token.value
    }

    /**
     * Fetch the CSRF token from the server
     */
    async function fetchCsrfToken() {
        const response = await axios.get('/api/csrf')
        updateFromHeaders(response.headers)
    }

    /**
     * Get the CSRF token name and value keys from config.
     */
    function getNameKey(): string {
        const config = useConfigStore()
        return config.get('csrf.name', 'csrf') + '_name'
    }
    function getValueKey(): string {
        const config = useConfigStore()
        return config.get('csrf.name', 'csrf') + '_value'
    }

    /**
     * Meta tag reader and writer
     */
    function readMetaTag(name: string): string {
        return document.querySelector("meta[name='" + name + "']")?.getAttribute('content') ?? ''
    }
    function writeMetaTag(name: string, value: string) {
        const metaTag = document.querySelector("meta[name='" + name + "']")
        if (metaTag) {
            metaTag.setAttribute('content', value)
        } else {
            const newMetaTag = document.createElement('meta')
            newMetaTag.setAttribute('name', name)
            newMetaTag.setAttribute('content', value)
            document.head.appendChild(newMetaTag)
        }
    }

    /**
     * Update the CSRF token with the values from the request headers.
     *
     * N.B.: CSRF keys are hardcoded with '{name}_name' and '{name}_value' in
     * PHP. However, the headers doesn't allows underscores that are replaced
     * with dashes automatically.
     */
    function updateFromHeaders(headers: any) {
        const config = useConfigStore()
        const nameKey = config.get('csrf.name', 'csrf') + '-name'
        const valueKey = config.get('csrf.name', 'csrf') + '-value'

        // Update both value only if the headers are present
        // This is to avoid overwriting the CSRF token with empty values
        if (nameKey in headers) {
            name.value = headers[nameKey]
        }
        if (valueKey in headers) {
            token.value = headers[valueKey]
        }
    }

    /**
     * Return if CSRF is enabled
     */
    function isEnabled(): boolean {
        const config = useConfigStore()
        return config.get('csrf.enabled', true)
    }

    /**
     * Watchers - Watch for changes in the CSRF token and update the axios
     * headers + meta tags
     */
    watchEffect(() => {
        if (isEnabled() && name.value !== '' && token.value !== '') {
            writeMetaTag(key_name.value, name.value)
            writeMetaTag(key_value.value, token.value)
            setAxiosHeader()
        }
    })

    /**
     * Export functions and managed states
     */
    return {
        key_name,
        key_value,
        name,
        token,
        isEnabled,
        updateFromHeaders,
        fetchCsrfToken
    }
}
