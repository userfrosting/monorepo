import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import GroupActivities from '../../../../components/Pages/Admin/Group/GroupActivities.vue'
import PermissionActivities from '../../../../components/Pages/Admin/Permission/PermissionActivities.vue'
import RoleActivities from '../../../../components/Pages/Admin/Role/RoleActivities.vue'

const row = {
    occurred_at: '2024-01-01T12:00:00Z',
    ip_address: '127.0.0.1',
    user: {
        user_name: 'jane',
        full_name: 'Jane Doe',
        email: 'jane@example.com'
    }
}

const stubs = {
    UFCardBox: {
        props: ['title'],
        template: '<section data-test="card" :data-title="title"><slot /></section>'
    },
    UFSprunjeTable: {
        props: ['dataUrl', 'defaultSorts'],
        template: `
            <div
                data-test="table"
                :data-url="dataUrl"
                :data-default-sorts="JSON.stringify(defaultSorts)">
                <header><slot name="header" /></header>
                <main><slot name="body" :row="row" /></main>
            </div>
        `,
        data: () => ({ row })
    },
    UFSprunjeHeader: {
        props: ['sort'],
        template: '<span data-test="header" :data-sort="sort"><slot /></span>'
    },
    UFSprunjeColumn: { template: '<div data-test="column"><slot /></div>' },
    ActivityDescription: {
        props: ['activity'],
        template: '<span data-test="description">{{ activity.user.full_name }}</span>'
    },
    RouterLink: {
        props: ['to'],
        template: '<a data-test="user-link" :data-user="to.params.user_name"><slot /></a>'
    }
}

const global = {
    mocks: {
        $t: (key: string) => key,
        $tdate: (value: string) => value
    },
    stubs
}

const components = [
    {
        component: GroupActivities,
        url: '/api/groups/g/admins/activities'
    },
    {
        component: PermissionActivities,
        url: '/api/permissions/p/admins/activities'
    },
    {
        component: RoleActivities,
        url: '/api/roles/r/admins/activities'
    }
]

describe('admin activity pages', () => {
    test.each(components)('renders activity table for $url', ({ component, url }) => {
        const wrapper = mount(component, {
            props: { slug: 'admins' },
            global
        })

        const table = wrapper.get('[data-test="table"]')
        expect(table.attributes('data-url')).toBe(url)
        expect(table.attributes('data-default-sorts')).toBe('{"occurred_at":"desc"}')
        expect(wrapper.findAll('[data-test="header"]')).toHaveLength(3)
        expect(wrapper.find('[data-test="description"]').text()).toBe('Jane Doe')
        expect(wrapper.find('[data-test="user-link"]').attributes('data-user')).toBe('jane')
        expect(wrapper.text()).toContain('2024-01-01T12:00:00Z')
        expect(wrapper.text()).toContain('127.0.0.1')
    })

    test.each(components)('hides activity table when slug is empty for $url', ({ component }) => {
        const wrapper = mount(component, {
            props: { slug: '' },
            global
        })

        expect(wrapper.find('[data-test="card"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="table"]').exists()).toBe(false)
    })
})
