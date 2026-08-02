import { describe, expect, test, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { h, ref } from 'vue'
import SprunjeColumn from '../../../components/Sprunjer/SprunjeColumn.vue'
import SprunjeDownload from '../../../components/Sprunjer/SprunjeDownload.vue'
import SprunjeFilters from '../../../components/Sprunjer/SprunjeFilters.vue'
import SprunjeHeader from '../../../components/Sprunjer/SprunjeHeader.vue'
import SprunjePaginator from '../../../components/Sprunjer/SprunjePaginator.vue'
import SprunjeSearch from '../../../components/Sprunjer/SprunjeSearch.vue'
import SprunjeTable from '../../../components/Sprunjer/SprunjeTable.vue'

const { useSprunjerMock } = vi.hoisted(() => ({
    useSprunjerMock: vi.fn()
}))

vi.mock('@userfrosting/sprinkle-core/composables', () => ({
    useSprunjer: (...args: unknown[]) => useSprunjerMock(...args)
}))

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useTranslator: () => ({
        translate: (key: string, params?: { term?: string }) =>
            params?.term ? `SPRUNJE.SEARCH:${params.term}` : key
    })
}))

describe('sprunjer components', () => {
    const fontAwesomeStub = { 'font-awesome-icon': { template: '<i />' } }

    beforeEach(() => {
        useSprunjerMock.mockReset()
    })

    test('renders a basic column slot', () => {
        const wrapper = mount(SprunjeColumn, {
            slots: {
                default: 'Cell Value'
            }
        })

        expect(wrapper.text()).toContain('Cell Value')
    })

    test('renders sortable header states and triggers sorting', async () => {
        const toggleSort = vi.fn()
        const sorts = ref<Record<string, string>>({ name: 'asc' })

        const wrapper = mount(SprunjeHeader, {
            props: { sort: 'name' },
            slots: { default: 'Name' },
            global: {
                provide: {
                    sprunjer: {
                        sorts,
                        toggleSort
                    }
                },
                stubs: {
                    'font-awesome-icon': { template: '<i />' }
                }
            }
        })

        await wrapper.get('a').trigger('click')
        expect(toggleSort).toHaveBeenCalledWith('name')

        sorts.value.name = 'desc'
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('Name')
    })

    test('renders the default sort icon for an unsorted column', () => {
        const wrapper = mount(SprunjeHeader, {
            props: { sort: 'name' },
            global: {
                provide: {
                    sprunjer: {
                        sorts: ref({}),
                        toggleSort: vi.fn()
                    }
                },
                stubs: {
                    'font-awesome-icon': {
                        props: ['icon'],
                        template: '<i :data-icon="icon[1]" />'
                    }
                }
            }
        })

        expect(wrapper.find('[data-icon="sort"]').exists()).toBe(true)
    })

    test('renders unsortable header when sort prop is absent', () => {
        const wrapper = mount(SprunjeHeader, {
            slots: { default: 'Plain Header' },
            global: {
                provide: {
                    sprunjer: {
                        sorts: ref({}),
                        toggleSort: vi.fn()
                    }
                },
                stubs: fontAwesomeStub
            }
        })

        expect(wrapper.find('a').exists()).toBe(false)
        expect(wrapper.text()).toContain('Plain Header')
    })

    test('binds search input to injected filters and supports custom label', async () => {
        const filters = ref<Record<string, string>>({})

        const wrapper = mount(SprunjeSearch, {
            props: {
                column: 'name'
            },
            global: {
                provide: {
                    sprunjer: {
                        filters
                    }
                }
            }
        })

        const input = wrapper.get('input[type="search"]')
        expect(input.attributes('placeholder')).toBe('SPRUNJE.SEARCH:name')

        await input.setValue('jane')
        expect(filters.value.name).toBe('jane')

        const labeled = mount(SprunjeSearch, {
            props: {
                column: 'name',
                label: 'Search Users'
            },
            global: {
                provide: {
                    sprunjer: {
                        filters
                    }
                }
            }
        })

        expect(labeled.get('input[type="search"]').attributes('placeholder')).toBe('Search Users')
    })

    test('renders filters, supports listable options and reset', async () => {
        const filters = ref<Record<string, string>>({ status: 'enabled', name: 'Jane' })
        const data = ref({
            filterable: ['status', 'name'],
            listable: {
                status: [
                    { value: 'enabled', text: 'Enabled' },
                    { value: 'disabled', text: 'Disabled' }
                ]
            }
        })

        const wrapper = mount(SprunjeFilters, {
            global: {
                mocks: {
                    $t: (key: string) => key
                },
                provide: {
                    sprunjer: {
                        filters,
                        data
                    }
                },
                stubs: {
                    'font-awesome-icon': { template: '<i />' }
                }
            }
        })

        expect(wrapper.find('select').exists()).toBe(true)
        expect(wrapper.find('input[type="text"]').exists()).toBe(true)

        await wrapper.get('input[type="text"]').setValue('Smith')
        expect(filters.value.name).toBe('Smith')

        await wrapper.get('button').trigger('click')
        expect(filters.value).toEqual({})
    })

    test('updates a listable filter selection', async () => {
        const filters = ref<Record<string, string>>({ status: '' })
        const data = ref({
            filterable: ['status'],
            listable: {
                status: [{ value: 'enabled', text: 'Enabled' }]
            }
        })

        const wrapper = mount(SprunjeFilters, {
            global: {
                mocks: { $t: (key: string) => key },
                provide: { sprunjer: { filters, data } },
                stubs: fontAwesomeStub
            }
        })

        expect(wrapper.findAll('option').map((option) => option.text())).toEqual(['', 'Enabled'])
        await wrapper.get('select').setValue('enabled')

        expect(filters.value.status).toBe('enabled')
    })

    test('updates page and page size from paginator controls', async () => {
        const sprunjer = {
            size: ref<number | string>(10),
            page: ref(0),
            totalPages: ref(2),
            countFiltered: ref(8),
            first: ref(1),
            last: ref(5)
        }

        const wrapper = mount(SprunjePaginator, {
            props: {
                rowsPerPageOptions: [5, 10]
            },
            global: {
                mocks: {
                    $t: (key: string) => key
                },
                provide: {
                    sprunjer
                },
                stubs: {
                    'font-awesome-icon': { template: '<i />' }
                }
            }
        })

        const links = wrapper.findAll('a.uk-icon-link')
        await links[0].trigger('click')
        expect(sprunjer.page.value).toBe(0)

        await links[2].trigger('click')
        expect(sprunjer.page.value).toBe(1)

        await links[3].trigger('click')
        expect(sprunjer.page.value).toBe(2)

        const selects = wrapper.findAll('select')
        await selects[1].setValue('5')
        expect(sprunjer.size.value).toBe(5)
    })

    test('uses default paginator options and supports previous-page navigation', async () => {
        const sprunjer = {
            size: ref<number | string>(10),
            page: ref(1),
            totalPages: ref(2),
            countFiltered: ref(8),
            first: ref(6),
            last: ref(8)
        }

        const wrapper = mount(SprunjePaginator, {
            global: {
                mocks: { $t: (key: string) => key },
                provide: { sprunjer },
                stubs: fontAwesomeStub
            }
        })

        expect(wrapper.findAll('select')[1].findAll('option')).toHaveLength(4)

        await wrapper.findAll('a.uk-icon-link')[1].trigger('click')

        expect(sprunjer.page.value).toBe(0)

        await wrapper.findAll('select')[0].setValue('2')

        expect(sprunjer.page.value).toBe(2)
    })

    test('invokes csv download from dropdown action', async () => {
        const downloadCsv = vi.fn()

        const wrapper = mount(SprunjeDownload, {
            global: {
                provide: {
                    sprunjer: {
                        downloadCsv
                    }
                },
                stubs: {
                    'font-awesome-icon': { template: '<i />' }
                }
            }
        })

        await wrapper.get('a[href="#"]').trigger('click')
        expect(downloadCsv).toHaveBeenCalledTimes(1)
    })

    test('renders table slots and toggles filter panel', async () => {
        const sprunjer = {
            rows: ref([{ id: 7, name: 'Alpha' }]),
            loading: ref(false),
            data: ref({ filterable: [], listable: {} }),
            filters: ref({}),
            sorts: ref({}),
            size: ref(10),
            page: ref(0),
            totalPages: ref(0),
            countFiltered: ref(1),
            first: ref(1),
            last: ref(1),
            fetch: vi.fn(),
            toggleSort: vi.fn(),
            downloadCsv: vi.fn()
        }

        useSprunjerMock.mockReturnValue(sprunjer)

        const wrapper = mount(SprunjeTable, {
            props: {
                dataUrl: '/api/test',
                searchColumn: 'name'
            },
            slots: {
                actions: '<button data-test="action">Action</button>',
                filters: ({ sprunjer }) =>
                    h('div', {
                        'data-test': 'named-filters',
                        'data-has-rows': String(sprunjer.rows.value.length > 0)
                    }),
                header: '<th data-test="header">Header</th>',
                body: '<td data-test="cell">Row</td>',
                filterPanel: '<div data-test="extra-filter">Extra</div>',
                paginator: '<div data-test="custom-paginator">Pager</div>'
            },
            global: {
                mocks: {
                    $t: (key: string) => key
                },
                stubs: {
                    'font-awesome-icon': { template: '<i />' },
                    SprunjeSearch: { template: '<div data-test="search" />' },
                    SprunjeFilters: { template: '<div data-test="filters" />' },
                    SprunjeDownload: { template: '<div data-test="download" />' }
                }
            }
        })

        expect(wrapper.find('[data-test="header"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="cell"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="search"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="named-filters"]').exists()).toBe(true)

        await wrapper.get('a.uk-button').trigger('click')
        expect(wrapper.find('[data-test="filters"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="extra-filter"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="custom-paginator"]').exists()).toBe(true)
    })

    test('renders loading and empty states', () => {
        useSprunjerMock.mockReturnValue({
            rows: ref([]),
            loading: ref(true),
            data: ref({ filterable: [], listable: {} }),
            filters: ref({}),
            sorts: ref({}),
            size: ref(10),
            page: ref(0),
            totalPages: ref(0),
            countFiltered: ref(0),
            first: ref(0),
            last: ref(0),
            fetch: vi.fn(),
            toggleSort: vi.fn(),
            downloadCsv: vi.fn()
        })

        const loadingWrapper = mount(SprunjeTable, {
            props: { dataUrl: '/api/test' },
            global: {
                mocks: {
                    $t: (key: string) => key
                },
                stubs: fontAwesomeStub
            }
        })

        expect(loadingWrapper.find('[uk-spinner="ratio: 2"]').exists()).toBe(true)

        useSprunjerMock.mockReturnValue({
            rows: ref([]),
            loading: ref(false),
            data: ref({ filterable: [], listable: {} }),
            filters: ref({}),
            sorts: ref({}),
            size: ref(10),
            page: ref(0),
            totalPages: ref(0),
            countFiltered: ref(0),
            first: ref(0),
            last: ref(0),
            fetch: vi.fn(),
            toggleSort: vi.fn(),
            downloadCsv: vi.fn()
        })

        const emptyWrapper = mount(SprunjeTable, {
            props: { dataUrl: '/api/test' },
            global: {
                mocks: {
                    $t: (key: string) => key
                },
                stubs: fontAwesomeStub
            }
        })

        expect(emptyWrapper.text()).toContain('SPRUNJE.NO_RESULTS')
    })

    test('hides optional table controls and forwards defaults', () => {
        const sprunjer = {
            rows: ref([{ id: 1 }]),
            loading: ref(false),
            data: ref({ filterable: [], listable: {} })
        }
        useSprunjerMock.mockReturnValue(sprunjer)

        const wrapper = mount(SprunjeTable, {
            props: {
                dataUrl: '/api/users',
                hidePagination: true,
                hideFilters: true,
                hideDownload: true,
                defaultSorts: { name: 'asc' },
                defaultFilters: { active: '1' },
                defaultSize: 'all',
                defaultPage: 2
            },
            global: {
                mocks: { $t: (key: string) => key },
                stubs: {
                    ...fontAwesomeStub,
                    SprunjeSearch: true,
                    SprunjeFilters: true,
                    SprunjeDownload: true,
                    SprunjePaginator: true
                }
            }
        })

        expect(wrapper.find('a.uk-button').exists()).toBe(false)
        expect(wrapper.findComponent({ name: 'SprunjeDownload' }).exists()).toBe(false)
        expect(wrapper.findComponent({ name: 'SprunjePaginator' }).exists()).toBe(false)
        const useSprunjerArgs = useSprunjerMock.mock.calls[
            useSprunjerMock.mock.calls.length - 1
        ] as [() => string, ...unknown[]]
        expect(useSprunjerArgs[0]()).toBe('/api/users')
        expect(useSprunjerMock).toHaveBeenCalledWith(
            expect.any(Function),
            { name: 'asc' },
            { active: '1' },
            'all',
            2
        )
    })
})
