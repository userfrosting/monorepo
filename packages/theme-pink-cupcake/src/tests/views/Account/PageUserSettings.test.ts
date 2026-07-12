import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import PageUserSettings from '../../../views/Account/PageUserSettings.vue'
import PageUserSettingsEmail from '../../../views/Account/PageUserSettingsEmail.vue'
import PageUserSettingsPassword from '../../../views/Account/PageUserSettingsPassword.vue'
import PageUserSettingsProfile from '../../../views/Account/PageUserSettingsProfile.vue'

const marker = (id: string) => ({
    template: `<div data-test="${id}" />`
})

describe('Account settings views', () => {
    test('renders settings layout with sidebar links and router view', () => {
        const wrapper = mount(PageUserSettings, {
            global: {
                mocks: {
                    $t: (key: string) => key
                },
                stubs: {
                    UFCardBox: { template: '<div><slot /></div>' },
                    UFSideBarItem: {
                        props: ['to', 'label'],
                        template:
                            '<div class="sidebar-item" :data-route="to.name" :data-label="label"></div>'
                    },
                    RouterView: marker('router-view')
                }
            }
        })

        const items = wrapper.findAll('.sidebar-item')
        expect(items).toHaveLength(3)
        expect(items.map((item) => item.attributes('data-route'))).toEqual([
            'account.settings.profile',
            'account.settings.email',
            'account.settings.password'
        ])
        expect(wrapper.find('[data-test="router-view"]').exists()).toBe(true)
    })

    test('renders email, password, and profile forms inside cards', () => {
        const email = mount(PageUserSettingsEmail, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFCardBox: { template: '<div><slot /></div>' },
                    FormUserEmail: marker('form-user-email')
                }
            }
        })
        const password = mount(PageUserSettingsPassword, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFCardBox: { template: '<div><slot /></div>' },
                    FormUserPassword: marker('form-user-password')
                }
            }
        })
        const profile = mount(PageUserSettingsProfile, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    UFCardBox: { template: '<div><slot /></div>' },
                    FormUserProfile: marker('form-user-profile')
                }
            }
        })

        expect(email.find('[data-test="form-user-email"]').exists()).toBe(true)
        expect(password.find('[data-test="form-user-password"]').exists()).toBe(true)
        expect(profile.find('[data-test="form-user-profile"]').exists()).toBe(true)
    })
})
