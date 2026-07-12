import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { UFAdminSidebarMenuItems } from '../../components'

describe('components/index.ts', () => {
    test('exports UFAdminSidebarMenuItems', () => {
        expect(UFAdminSidebarMenuItems).toBeDefined()
    })

    test('renders all sidebar items when access is allowed', () => {
        const wrapper = mount(UFAdminSidebarMenuItems, {
            global: {
                mocks: {
                    $checkAccess: () => true,
                    $t: (key: string) => key
                },
                stubs: {
                    UFSideBarItem: {
                        props: ['to', 'label'],
                        template:
                            '<div class="sidebar-item" :data-route="to.name" :data-label="label" />'
                    }
                }
            }
        })

        const items = wrapper.findAll('.sidebar-item')

        expect(items).toHaveLength(7)
        expect(items.map((item) => item.attributes('data-route'))).toEqual([
            'admin.dashboard',
            'admin.users',
            'admin.activities',
            'admin.roles',
            'admin.permissions',
            'admin.groups',
            'admin.config'
        ])
    })

    test('renders only config when only cache/system permissions are allowed', () => {
        const wrapper = mount(UFAdminSidebarMenuItems, {
            global: {
                mocks: {
                    $checkAccess: (permission: string) =>
                        permission === 'view_system_info' || permission === 'clear_cache',
                    $t: (key: string) => key
                },
                stubs: {
                    UFSideBarItem: {
                        props: ['to'],
                        template: '<div class="sidebar-item" :data-route="to.name" />'
                    }
                }
            }
        })

        const items = wrapper.findAll('.sidebar-item')
        expect(items).toHaveLength(1)
        expect(items[0].attributes('data-route')).toBe('admin.config')
    })

    test('renders no items when access is denied', () => {
        const wrapper = mount(UFAdminSidebarMenuItems, {
            global: {
                mocks: {
                    $checkAccess: () => false,
                    $t: (key: string) => key
                },
                stubs: {
                    UFSideBarItem: {
                        template: '<div class="sidebar-item" />'
                    }
                }
            }
        })

        expect(wrapper.findAll('.sidebar-item')).toHaveLength(0)
    })
})
