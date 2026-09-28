import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import ActivityDescription from '../../../components/Activities/ActivityDescription.vue'

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useTranslator: () => ({
        translate: (key: string) => {
            const translations: Record<string, string> = {
                en_US: 'English',
                fr_FR: 'French'
            }

            return translations[key] ?? key
        }
    })
}))

const global = {
    mocks: {
        $t: (key: string) => {
            const translations: Record<string, string> = {
                FIRST_NAME: 'First name',
                LAST_NAME: 'Last name',
                LOCALE: 'Locale',
                'LOCALE.EN_US': 'English',
                'LOCALE.FR_FR': 'French'
            }

            return translations[key] ?? key
        }
    }
}

describe('ActivityDescription.vue', () => {
    test('renders changed properties', () => {
        const wrapper = mount(ActivityDescription, {
            props: {
                activity: {
                    description: 'Profile updated',
                    properties: {
                        first_name: { old: 'Admin', new: 'AdmiN' },
                        locale: { old: 'fr_FR', new: 'en_US' }
                    }
                }
            },
            global
        })

        expect(wrapper.text()).toContain('Profile updated')
        expect(wrapper.text()).toContain('First name: Admin → AdmiN')
        expect(wrapper.text()).toContain('Locale: French → English')
    })

    test('renders change values as text', () => {
        const wrapper = mount(ActivityDescription, {
            props: {
                activity: {
                    description: 'Profile updated',
                    properties: {
                        first_name: {
                            old: '<script>alert(1)</script>',
                            new: 'safe'
                        }
                    }
                }
            },
            global
        })

        expect(wrapper.text()).toContain('<script>alert(1)</script>')
        expect(wrapper.html()).not.toContain('<script>alert(1)</script>')
    })

    test('falls back to the generic description for unknown activities', () => {
        const wrapper = mount(ActivityDescription, {
            props: {
                activity: {
                    description: '<strong>Legacy activity</strong>'
                }
            },
            global
        })

        expect(wrapper.find('strong').text()).toBe('Legacy activity')
    })

    test('renders only the description when there are no properties', () => {
        const wrapper = mount(ActivityDescription, {
            props: {
                activity: {
                    description: 'Profile updated',
                    properties: {}
                }
            },
            global
        })

        expect(wrapper.text()).toBe('Profile updated')
        expect(wrapper.findAll('strong')).toHaveLength(0)
    })

    test('renders a property snapshot', () => {
        const wrapper = mount(ActivityDescription, {
            props: {
                activity: {
                    description: 'Profile updated',
                    properties: {
                        first_name: { old: 'Admin', new: 'AdmiN' }
                    }
                }
            },
            global
        })

        expect(wrapper.text()).toContain('First name: Admin → AdmiN')
    })

    test('formats empty and structured property values', () => {
        const wrapper = mount(ActivityDescription, {
            props: {
                activity: {
                    description: 'Settings updated',
                    properties: {
                        empty: { old: null, new: '' },
                        missing: { old: undefined, new: { enabled: true } },
                        symbol: { old: Symbol('legacy'), new: Symbol('current') }
                    }
                }
            },
            global
        })

        expect(wrapper.text()).toContain('EMPTY: - → -')
        expect(wrapper.text()).toContain('MISSING: - → {"enabled":true}')
        expect(wrapper.text()).toContain('SYMBOL: - → -')
    })
})
