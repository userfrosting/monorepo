import { beforeEach, describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref, type Ref } from 'vue'
import type {
    GroupInterface,
    RoleInterface,
    UserInterface
} from '@userfrosting/sprinkle-account/interfaces'
import GroupCreateModal from '../../../../components/Pages/Admin/Group/GroupCreateModal.vue'
import GroupEditModal from '../../../../components/Pages/Admin/Group/GroupEditModal.vue'
import GroupDeleteModal from '../../../../components/Pages/Admin/Group/GroupDeleteModal.vue'
import GroupForm from '../../../../components/Pages/Admin/Group/GroupForm.vue'
import RoleCreateModal from '../../../../components/Pages/Admin/Role/RoleCreateModal.vue'
import RoleEditModal from '../../../../components/Pages/Admin/Role/RoleEditModal.vue'
import RoleDeleteModal from '../../../../components/Pages/Admin/Role/RoleDeleteModal.vue'
import RoleManagePermissionModal from '../../../../components/Pages/Admin/Role/RoleManagePermissionModal.vue'
import RoleForm from '../../../../components/Pages/Admin/Role/RoleForm.vue'
import UserCreateModal from '../../../../components/Pages/Admin/User/UserCreateModal.vue'
import UserEditModal from '../../../../components/Pages/Admin/User/UserEditModal.vue'
import UserDeleteModal from '../../../../components/Pages/Admin/User/UserDeleteModal.vue'
import UserActivateModal from '../../../../components/Pages/Admin/User/UserActivateModal.vue'
import UserManageRolesModal from '../../../../components/Pages/Admin/User/UserManageRolesModal.vue'
import UserPasswordModal from '../../../../components/Pages/Admin/User/UserPasswordModal.vue'
import UserPasswordResetModal from '../../../../components/Pages/Admin/User/UserPasswordResetModal.vue'
import UserForm from '../../../../components/Pages/Admin/User/UserForm.vue'
import UserPasswordForm from '../../../../components/Pages/Admin/User/UserPasswordForm.vue'

const { hideMock, modalMock, apiMocks } = vi.hoisted(() => ({
    hideMock: vi.fn(),
    modalMock: vi.fn(),
    apiMocks: {
        useGroupApi: vi.fn(),
        useRoleApi: vi.fn(),
        useUserApi: vi.fn(),
        useGroupsApi: vi.fn(),
        useUserUpdateApi: vi.fn(),
        useUserRolesApi: vi.fn(),
        useRolePermissionsApi: vi.fn(),
        useUserPasswordResetApi: vi.fn(),
        useRoleUpdateApi: vi.fn()
    }
}))

vi.mock('uikit', () => ({
    default: {
        modal: (...args: unknown[]) => modalMock(...args)
    }
}))

vi.mock('@userfrosting/sprinkle-admin/composables', () => ({
    useGroupApi: () => apiMocks.useGroupApi(),
    useRoleApi: () => apiMocks.useRoleApi(),
    useUserApi: () => apiMocks.useUserApi(),
    useGroupsApi: () => apiMocks.useGroupsApi(),
    useUserUpdateApi: () => apiMocks.useUserUpdateApi(),
    useUserRolesApi: () => apiMocks.useUserRolesApi(),
    useRolePermissionsApi: () => apiMocks.useRolePermissionsApi(),
    useUserPasswordResetApi: () => apiMocks.useUserPasswordResetApi(),
    useRoleUpdateApi: () => apiMocks.useRoleUpdateApi()
}))

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useConfigStore: () => ({
        get: (path: string, fallback?: string) => {
            if (path === 'locales.available') {
                return { en_US: 'English (US)', fr_FR: 'French' }
            }
            if (path === 'site.registration.user_defaults.locale') {
                return 'fr_FR'
            }

            return fallback
        }
    })
}))

const flushPromises = async () => {
    await Promise.resolve()
}

const baseGroup: GroupInterface = {
    id: 1,
    slug: 'admins',
    name: 'Admins',
    description: 'Admin group',
    icon: 'users',
    created_at: '',
    updated_at: '',
    deleted_at: null
}

const baseRole: RoleInterface = {
    id: 2,
    slug: 'managers',
    name: 'Managers',
    description: 'Manager role',
    created_at: '',
    updated_at: '',
    deleted_at: null
}

const baseUser: UserInterface = {
    id: 10,
    user_name: 'jane',
    first_name: 'Jane',
    last_name: 'Doe',
    full_name: 'Jane Doe',
    email: 'jane@example.com',
    avatar: '/avatar.png',
    flag_enabled: true,
    flag_verified: false,
    group_id: 1,
    locale: 'en_US',
    created_at: '',
    updated_at: '',
    deleted_at: null
}

const makeValidation = (valid = true) => ({
    $error: false,
    name: { $error: false },
    slug: { $error: false },
    icon: { $error: false },
    description: { $error: false },
    user_name: { $error: false },
    group_id: { $error: false },
    first_name: { $error: false },
    last_name: { $error: false },
    email: { $error: false },
    locale: { $error: false },
    $errors: {
        name: [],
        slug: [],
        icon: [],
        description: [],
        user_name: [],
        group_id: [],
        first_name: [],
        last_name: [],
        email: [],
        locale: []
    },
    $validate: vi.fn().mockResolvedValue({ valid })
})

let groupFormData: Ref<{ slug: string; name: string; description: string; icon: string }>
let roleFormData: Ref<{ slug: string; name: string; description: string }>
let userFormData: Ref<{
    user_name: string
    group_id: number | null
    first_name: string
    last_name: string
    email: string
    locale: string
}>
let groupSlugLocked: Ref<boolean>
let roleSlugLocked: Ref<boolean>

const createGroup = vi.fn()
const updateGroup = vi.fn()
const deleteGroup = vi.fn()
const resetGroupForm = vi.fn()
const createRole = vi.fn()
const updateRole = vi.fn()
const deleteRole = vi.fn()
const resetRoleForm = vi.fn()
const createUser = vi.fn()
const updateUser = vi.fn()
const deleteUser = vi.fn()
const resetUserForm = vi.fn()
const updateGroups = vi.fn()
const submitUserUpdate = vi.fn()
const fetchUserRoles = vi.fn()
const fetchRolePermissions = vi.fn()
const passwordReset = vi.fn()
const submitRoleUpdate = vi.fn()

const selectedRoles = ref<number[]>([])
const selectedPermissions = ref<number[]>([])

const globalStubs = {
    UFModal: {
        template:
            '<div><slot name="header" /><slot /><slot name="default" /><slot name="footer" /></div>'
    },
    UFModalConfirmation: {
        emits: ['confirmed'],
        template:
            '<button data-test="confirm" @click="$emit(\'confirmed\')"><slot name="prompt" /></button>'
    },
    UFFormValidationError: { template: '<div data-test="validation" />' },
    UFSprunjeHeader: { template: '<div><slot /></div>' },
    UFSprunjeColumn: { template: '<div><slot /></div>' },
    UFSprunjeTable: {
        template: `
            <div>
                <slot name="header" :sprunjer="sprunjer" />
                <slot name="body" :row="row" />
            </div>
        `,
        data() {
            return {
                sprunjer: {
                    rows: {
                        value: [
                            {
                                id: 1,
                                name: 'One',
                                description: 'D1',
                                slug: 'one',
                                conditions: 'always()'
                            },
                            {
                                id: 2,
                                name: 'Two',
                                description: 'D2',
                                slug: 'two',
                                conditions: 'always()'
                            }
                        ]
                    }
                },
                row: { id: 1, name: 'One', description: 'D1', slug: 'one', conditions: 'always()' }
            }
        }
    },
    'font-awesome-icon': { template: '<i />' }
}

beforeEach(() => {
    hideMock.mockReset()
    modalMock.mockReset()
    modalMock.mockReturnValue({ hide: hideMock })

    groupFormData = ref({ slug: '', name: '', description: '', icon: 'users' })
    roleFormData = ref({ slug: '', name: '', description: '' })
    userFormData = ref({
        user_name: '',
        group_id: 0,
        first_name: '',
        last_name: '',
        email: '',
        locale: 'en_US'
    })
    groupSlugLocked = ref(true)
    roleSlugLocked = ref(true)
    selectedRoles.value = [1]
    selectedPermissions.value = [1]

    createGroup.mockReset().mockResolvedValue(undefined)
    updateGroup.mockReset().mockResolvedValue(undefined)
    deleteGroup.mockReset().mockResolvedValue(undefined)
    resetGroupForm.mockReset()
    createRole.mockReset().mockResolvedValue(undefined)
    updateRole.mockReset().mockResolvedValue(undefined)
    deleteRole.mockReset().mockResolvedValue(undefined)
    resetRoleForm.mockReset()
    createUser.mockReset().mockResolvedValue(undefined)
    updateUser.mockReset().mockResolvedValue(undefined)
    deleteUser.mockReset().mockResolvedValue(undefined)
    resetUserForm.mockReset()
    updateGroups.mockReset()
    submitUserUpdate.mockReset().mockResolvedValue(undefined)
    fetchUserRoles.mockReset()
    fetchRolePermissions.mockReset()
    passwordReset.mockReset()
    submitRoleUpdate.mockReset().mockResolvedValue(undefined)

    apiMocks.useGroupApi.mockReturnValue({
        createGroup,
        updateGroup,
        deleteGroup,
        r$: makeValidation(true),
        formData: groupFormData,
        apiLoading: ref(false),
        resetForm: resetGroupForm,
        slugLocked: groupSlugLocked
    })
    apiMocks.useRoleApi.mockReturnValue({
        createRole,
        updateRole,
        deleteRole,
        r$: makeValidation(true),
        formData: roleFormData,
        apiLoading: ref(false),
        resetForm: resetRoleForm,
        slugLocked: roleSlugLocked
    })
    apiMocks.useUserApi.mockReturnValue({
        createUser,
        updateUser,
        deleteUser,
        r$: makeValidation(true),
        formData: userFormData,
        apiLoading: ref(false),
        resetForm: resetUserForm
    })
    apiMocks.useGroupsApi.mockReturnValue({
        groups: ref([baseGroup]),
        updateGroups
    })
    apiMocks.useUserUpdateApi.mockReturnValue({
        submitUserUpdate,
        apiLoading: ref(false),
        apiError: ref(null)
    })
    apiMocks.useUserRolesApi.mockReturnValue({
        loading: ref(false),
        selected: selectedRoles,
        fetch: fetchUserRoles
    })
    apiMocks.useRolePermissionsApi.mockReturnValue({
        loading: ref(false),
        selected: selectedPermissions,
        fetch: fetchRolePermissions
    })
    apiMocks.useUserPasswordResetApi.mockReturnValue({
        passwordReset,
        apiLoading: ref(false),
        apiError: ref(null)
    })
    apiMocks.useRoleUpdateApi.mockReturnValue({
        submitRoleUpdate,
        apiLoading: ref(false),
        apiError: ref(null)
    })
})

describe('admin modal components', () => {
    test('handles group modal create/edit/delete flows', async () => {
        const createWrapper = mount(GroupCreateModal, {
            global: {
                stubs: {
                    ...globalStubs,
                    GroupForm: {
                        emits: ['success'],
                        template: '<button data-test="group-create" @click="$emit(\'success\')" />'
                    }
                },
                mocks: { $t: (key: string) => key }
            }
        })
        await createWrapper.get('[data-test="group-create"]').trigger('click')
        expect(createWrapper.emitted('saved')).toHaveLength(1)
        expect(modalMock).toHaveBeenCalledWith('#modal-group-create')

        const editWrapper = mount(GroupEditModal, {
            props: { group: baseGroup },
            global: {
                stubs: {
                    ...globalStubs,
                    GroupForm: {
                        emits: ['success'],
                        template: '<button data-test="group-edit" @click="$emit(\'success\')" />'
                    }
                },
                mocks: { $t: (key: string) => key }
            }
        })
        await editWrapper.get('[data-test="group-edit"]').trigger('click')
        expect(editWrapper.emitted('saved')).toHaveLength(1)
        expect(modalMock).toHaveBeenCalledWith('#modal-group-edit-1')

        const deleteWrapper = mount(GroupDeleteModal, {
            props: { group: baseGroup },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        expect(deleteWrapper.find('[data-test="confirm"]').exists()).toBe(true)
        expect(deleteWrapper.find('[data-test="confirm"]').text()).toBe('GROUP.DELETE_CONFIRM')
        await deleteWrapper.get('[data-test="confirm"]').trigger('click')
        await flushPromises()
        expect(deleteGroup).toHaveBeenCalledWith('admins')
        expect(deleteWrapper.emitted('deleted')).toHaveLength(1)
    })

    test('handles role modal create/edit/delete flows', async () => {
        const createWrapper = mount(RoleCreateModal, {
            global: {
                stubs: {
                    ...globalStubs,
                    RoleForm: {
                        emits: ['success'],
                        template: '<button data-test="role-create" @click="$emit(\'success\')" />'
                    }
                },
                mocks: { $t: (key: string) => key }
            }
        })
        await createWrapper.get('[data-test="role-create"]').trigger('click')
        expect(createWrapper.emitted('saved')).toHaveLength(1)
        expect(modalMock).toHaveBeenCalledWith('#modal-role-create')

        const editWrapper = mount(RoleEditModal, {
            props: { role: baseRole },
            global: {
                stubs: {
                    ...globalStubs,
                    RoleForm: {
                        emits: ['success'],
                        template: '<button data-test="role-edit" @click="$emit(\'success\')" />'
                    }
                },
                mocks: { $t: (key: string) => key }
            }
        })
        await editWrapper.get('[data-test="role-edit"]').trigger('click')
        expect(editWrapper.emitted('saved')).toHaveLength(1)
        expect(modalMock).toHaveBeenCalledWith('#modal-role-edit-2')

        const deleteWrapper = mount(RoleDeleteModal, {
            props: { role: baseRole },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        expect(deleteWrapper.find('[data-test="confirm"]').exists()).toBe(true)
        expect(deleteWrapper.find('[data-test="confirm"]').text()).toBe('ROLE.DELETE_CONFIRM')
        await deleteWrapper.get('[data-test="confirm"]').trigger('click')
        await flushPromises()
        expect(deleteRole).toHaveBeenCalledWith('managers')
        expect(deleteWrapper.emitted('deleted')).toHaveLength(1)
    })

    test('handles user modal create/edit/delete/password/reset flows', async () => {
        const createWrapper = mount(UserCreateModal, {
            global: {
                stubs: {
                    ...globalStubs,
                    UserForm: {
                        emits: ['success'],
                        template: '<button data-test="user-create" @click="$emit(\'success\')" />'
                    }
                },
                mocks: { $t: (key: string) => key }
            }
        })
        await createWrapper.get('a').trigger('click')
        expect(updateGroups).toHaveBeenCalledTimes(1)
        await createWrapper.get('[data-test="user-create"]').trigger('click')
        expect(createWrapper.emitted('saved')).toHaveLength(1)
        expect(modalMock).toHaveBeenCalledWith('#modal-user-create')

        const editWrapper = mount(UserEditModal, {
            props: { user: baseUser },
            global: {
                stubs: {
                    ...globalStubs,
                    UserForm: {
                        emits: ['success'],
                        template: '<button data-test="user-edit" @click="$emit(\'success\')" />'
                    }
                },
                mocks: { $t: (key: string) => key }
            }
        })
        await editWrapper.get('a').trigger('click')
        expect(updateGroups).toHaveBeenCalledTimes(2)
        await editWrapper.get('[data-test="user-edit"]').trigger('click')
        expect(editWrapper.emitted('saved')).toHaveLength(1)
        expect(modalMock).toHaveBeenCalledWith('#modal-user-edit-10')

        const deleteWrapper = mount(UserDeleteModal, {
            props: { user: baseUser },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await deleteWrapper.get('[data-test="confirm"]').trigger('click')
        await flushPromises()
        expect(deleteUser).toHaveBeenCalledWith('jane')
        expect(deleteWrapper.emitted('deleted')).toHaveLength(1)

        const passwordWrapper = mount(UserPasswordModal, {
            props: { user: baseUser },
            global: {
                stubs: {
                    ...globalStubs,
                    UserPasswordForm: {
                        emits: ['submit'],
                        template:
                            '<button data-test="password-submit" @click="$emit(\'submit\')" />'
                    }
                },
                mocks: { $t: (key: string) => key }
            }
        })
        await passwordWrapper.get('[data-test="password-submit"]').trigger('click')
        await flushPromises()
        expect(submitUserUpdate).toHaveBeenCalledWith('jane', 'password', {
            password: '',
            passwordc: ''
        })
        expect(modalMock).toHaveBeenCalledWith('#modal-user-password-10')

        await passwordWrapper.setProps({ user: { ...baseUser, user_name: 'john' } })

        const realPasswordWrapper = mount(UserPasswordModal, {
            props: { user: baseUser },
            global: {
                stubs: {
                    ...globalStubs,
                    UFModal: { template: '<div><slot name="default" /></div>' }
                },
                mocks: { $t: (key: string) => key }
            }
        })
        const realPasswordForm = realPasswordWrapper.findComponent(UserPasswordForm)
        expect(realPasswordForm.exists()).toBe(true)
        await realPasswordForm.get('input[data-test="password"]').setValue('new-password')
        await realPasswordForm.get('input[data-test="passwordc"]').setValue('new-password')
        realPasswordForm.vm.$emit('update:modelValue', {
            password: 'new-password',
            passwordc: 'new-password'
        })
        realPasswordForm.vm.$emit('submit')
        await flushPromises()
        expect(submitUserUpdate).toHaveBeenCalledWith('jane', 'password', {
            password: 'new-password',
            passwordc: 'new-password'
        })

        const passwordResetWrapper = mount(UserPasswordResetModal, {
            props: { user: baseUser },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await passwordResetWrapper.get('[data-test="confirm"]').trigger('click')
        expect(passwordReset).toHaveBeenCalledWith('jane')
    })

    test('handles user activation state actions and payloads', async () => {
        const activateWrapper = mount(UserActivateModal, {
            props: {
                user: {
                    ...baseUser,
                    flag_verified: false,
                    flag_enabled: true
                }
            },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        expect(activateWrapper.find('a[href="#confirm-user-activate-10"]').exists()).toBe(true)
        await activateWrapper.get('[data-test="confirm"]').trigger('click')
        await flushPromises()
        expect(submitUserUpdate).toHaveBeenCalledWith('jane', 'flag_verified', {
            flag_verified: '1'
        })
        expect(activateWrapper.emitted('saved')).toHaveLength(1)

        const disableWrapper = mount(UserActivateModal, {
            props: {
                user: {
                    ...baseUser,
                    flag_verified: true,
                    flag_enabled: true
                }
            },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        expect(disableWrapper.find('a[href="#confirm-user-disable-10"]').exists()).toBe(true)
        await disableWrapper.findAll('[data-test="confirm"]')[1].trigger('click')
        await flushPromises()
        expect(submitUserUpdate).toHaveBeenCalledWith('jane', 'flag_enabled', {
            flag_enabled: '0'
        })

        const enableWrapper = mount(UserActivateModal, {
            props: {
                user: {
                    ...baseUser,
                    flag_verified: true,
                    flag_enabled: false
                }
            },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        expect(enableWrapper.find('a[href="#confirm-user-enable-10"]').exists()).toBe(true)
        await enableWrapper.findAll('[data-test="confirm"]')[2].trigger('click')
        await flushPromises()
        expect(submitUserUpdate).toHaveBeenCalledWith('jane', 'flag_enabled', {
            flag_enabled: '1'
        })
    })

    test('handles role and user bulk assignment modals', async () => {
        const rolePermissionWrapper = mount(RoleManagePermissionModal, {
            props: { role: baseRole },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })

        await rolePermissionWrapper.get('a').trigger('click')
        expect(fetchRolePermissions).toHaveBeenCalledWith('managers')

        const roleCheckboxes = rolePermissionWrapper.findAll('input[type="checkbox"]')
        await roleCheckboxes[0].setValue(true)
        expect(selectedPermissions.value).toEqual([1, 2])
        await roleCheckboxes[0].setValue(false)
        expect(selectedPermissions.value).toEqual([])
        await roleCheckboxes[1].setValue(true)
        await roleCheckboxes[1].setValue(false)

        await rolePermissionWrapper.get('button.uk-button-primary').trigger('click')
        await flushPromises()
        expect(submitRoleUpdate).toHaveBeenCalledWith('managers', 'permissions', {
            permissions: []
        })
        expect(rolePermissionWrapper.emitted('saved')).toHaveLength(1)

        const userRolesWrapper = mount(UserManageRolesModal, {
            props: { user: baseUser },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })

        await userRolesWrapper.get('a').trigger('click')
        expect(fetchUserRoles).toHaveBeenCalledWith('jane')

        const userCheckboxes = userRolesWrapper.findAll('input[type="checkbox"]')
        await userCheckboxes[0].setValue(true)
        expect(selectedRoles.value).toEqual([1, 2])
        await userCheckboxes[0].setValue(false)
        expect(selectedRoles.value).toEqual([])

        await userRolesWrapper.get('button.uk-button-primary').trigger('click')
        await flushPromises()
        expect(submitUserUpdate).toHaveBeenCalledWith('jane', 'roles', {
            roles: []
        })
        expect(userRolesWrapper.emitted('saved')).toHaveLength(1)
    })

    test('normalizes string role IDs in the user roles v-model', async () => {
        selectedRoles.value = []

        const wrapper = mount(UserManageRolesModal, {
            props: { user: baseUser },
            global: {
                stubs: {
                    ...globalStubs,
                    UFSprunjeTable: {
                        template:
                            '<div><slot name="header" :sprunjer="sprunjer" /><slot name="body" :row="row" /></div>',
                        data: () => ({
                            sprunjer: { rows: { value: [{ id: '2' }] } },
                            row: { id: '2', name: 'Role', description: 'Description' }
                        })
                    }
                },
                mocks: { $t: (key: string) => key }
            }
        })

        await wrapper.findAll('input[type="checkbox"]')[1].setValue(true)

        expect(selectedRoles.value).toEqual([2])
    })

    test('swallows role permission update failures', async () => {
        submitRoleUpdate.mockRejectedValueOnce(new Error('Permission update failed'))

        const wrapper = mount(RoleManagePermissionModal, {
            props: { role: baseRole },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await wrapper.get('button.uk-button-primary').trigger('click')
        await flushPromises()

        expect(wrapper.emitted('saved')).toBeFalsy()
    })

    test('swallows group and role delete errors without emitting events', async () => {
        deleteGroup.mockRejectedValueOnce(new Error('Delete group failed'))
        deleteRole.mockRejectedValueOnce(new Error('Delete role failed'))

        const groupWrapper = mount(GroupDeleteModal, {
            props: { group: baseGroup },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await groupWrapper.get('[data-test="confirm"]').trigger('click')
        await flushPromises()
        expect(groupWrapper.emitted('deleted')).toBeFalsy()

        const roleWrapper = mount(RoleDeleteModal, {
            props: { role: baseRole },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await roleWrapper.get('[data-test="confirm"]').trigger('click')
        await flushPromises()
        expect(roleWrapper.emitted('deleted')).toBeFalsy()
    })

    test('swallows user delete errors without emitting an event', async () => {
        deleteUser.mockRejectedValueOnce(new Error('Delete user failed'))

        const wrapper = mount(UserDeleteModal, {
            props: { user: baseUser },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await wrapper.get('[data-test="confirm"]').trigger('click')
        await flushPromises()

        expect(wrapper.emitted('deleted')).toBeFalsy()
    })
})

describe('admin form components', () => {
    test('submits group form create and edit, and toggles slug lock', async () => {
        const createWrapper = mount(GroupForm, {
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })

        await createWrapper.get('input[data-test="name"]').setValue('New Group')
        await createWrapper.get('button.uk-form-button').trigger('click')
        await createWrapper.get('input[data-test="slug"]').setValue('new-group')
        await createWrapper.get('input[data-test="icon"]').setValue('')
        await createWrapper.get('textarea[data-test="description"]').setValue('Description')
        await createWrapper.get('form').trigger('submit.prevent')
        await flushPromises()

        expect(createGroup).toHaveBeenCalledWith(groupFormData.value)
        expect(createWrapper.emitted('success')).toHaveLength(1)
        expect(resetGroupForm).toHaveBeenCalledTimes(1)

        expect(groupSlugLocked.value).toBe(false)

        const editWrapper = mount(GroupForm, {
            props: { group: baseGroup },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await editWrapper.get('form').trigger('submit.prevent')
        await flushPromises()
        expect(updateGroup).toHaveBeenCalledWith('admins', groupFormData.value)
    })

    test('prevents submit for invalid role form and then submits update when valid', async () => {
        const invalidValidation = makeValidation(false)
        apiMocks.useRoleApi.mockReturnValueOnce({
            createRole,
            updateRole,
            deleteRole,
            r$: invalidValidation,
            formData: roleFormData,
            apiLoading: ref(false),
            resetForm: resetRoleForm,
            slugLocked: roleSlugLocked
        })

        const invalidWrapper = mount(RoleForm, {
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await invalidWrapper.get('form').trigger('submit.prevent')
        await flushPromises()
        expect(createRole).not.toHaveBeenCalled()

        const validWrapper = mount(RoleForm, {
            props: { role: baseRole },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await validWrapper.get('input[data-test="name"]').setValue('Updated Role')
        await validWrapper.get('form').trigger('submit.prevent')
        await flushPromises()
        expect(updateRole).toHaveBeenCalledWith('managers', roleFormData.value)
    })

    test('role slug lock toggle', async () => {
        const wrapper = mount(RoleForm, {
            props: { role: baseRole },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        expect(roleSlugLocked.value).toBe(true)
        await wrapper.get('button.uk-form-button').trigger('click')
        expect(roleSlugLocked.value).toBe(false)
        await wrapper.get('input[data-test="slug"]').setValue('updated-role')
        await wrapper.get('textarea[data-test="description"]').setValue('Updated description')
    })

    test('swallows role form create/update API failures', async () => {
        createRole.mockRejectedValueOnce(new Error('create failed'))
        updateRole.mockRejectedValueOnce(new Error('update failed'))

        const createWrapper = mount(RoleForm, {
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await createWrapper.get('form').trigger('submit.prevent')
        await flushPromises()
        expect(createWrapper.emitted('success')).toBeFalsy()

        const updateWrapper = mount(RoleForm, {
            props: { role: baseRole },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })
        await updateWrapper.get('form').trigger('submit.prevent')
        await flushPromises()
        expect(updateWrapper.emitted('success')).toBeFalsy()
    })

    test('swallows group form API failures and prevents invalid submission', async () => {
        const invalidValidation = makeValidation(false)
        apiMocks.useGroupApi.mockReturnValueOnce({
            createGroup,
            updateGroup,
            r$: invalidValidation,
            formData: groupFormData,
            apiLoading: ref(false),
            resetForm: resetGroupForm,
            slugLocked: groupSlugLocked
        })

        const invalidWrapper = mount(GroupForm, {
            global: { stubs: globalStubs, mocks: { $t: (key: string) => key } }
        })
        await invalidWrapper.get('form').trigger('submit.prevent')
        expect(createGroup).not.toHaveBeenCalled()

        createGroup.mockRejectedValueOnce(new Error('create failed'))
        const failedWrapper = mount(GroupForm, {
            global: { stubs: globalStubs, mocks: { $t: (key: string) => key } }
        })
        await failedWrapper.get('form').trigger('submit.prevent')
        await flushPromises()
        expect(failedWrapper.emitted('success')).toBeFalsy()
    })

    test('submits user form create and edit and applies default locale for create', async () => {
        const wrapper = mount(UserForm, {
            props: { groups: [baseGroup] },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })

        expect(userFormData.value.locale).toBe('fr_FR')

        await wrapper.get('input[data-test="user_name"]').setValue('newuser')
        await wrapper.get('input[data-test="first_name"]').setValue('New')
        await wrapper.get('input[data-test="last_name"]').setValue('User')
        await wrapper.get('input[data-test="email"]').setValue('new@example.com')
        await wrapper.get('select[data-test="group"]').setValue('1')
        await wrapper.get('select[data-test="locale"]').setValue('en_US')
        await wrapper.get('form').trigger('submit.prevent')
        await flushPromises()
        expect(createUser).toHaveBeenCalledWith(userFormData.value)

        const editWrapper = mount(UserForm, {
            props: {
                groups: [baseGroup],
                user: {
                    user_name: 'jane',
                    group_id: 1,
                    first_name: 'Jane',
                    last_name: 'Doe',
                    email: 'jane@example.com',
                    locale: 'en_US'
                }
            },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })

        expect(editWrapper.get('input[data-test="user_name"]').attributes('disabled')).toBeDefined()
        await editWrapper.get('button.uk-form-button').trigger('click')
        expect(
            editWrapper.get('input[data-test="user_name"]').attributes('disabled')
        ).toBeUndefined()

        await editWrapper.get('form').trigger('submit.prevent')
        await flushPromises()
        expect(updateUser).toHaveBeenCalledWith('jane', userFormData.value)
    })

    test('prevents invalid user submission and swallows create failures', async () => {
        const invalidValidation = makeValidation(false)
        apiMocks.useUserApi.mockReturnValueOnce({
            createUser,
            updateUser,
            r$: invalidValidation,
            formData: userFormData,
            apiLoading: ref(false),
            resetForm: resetUserForm
        })

        const invalidWrapper = mount(UserForm, {
            props: { groups: [baseGroup] },
            global: { stubs: globalStubs, mocks: { $t: (key: string) => key } }
        })
        await invalidWrapper.get('form').trigger('submit.prevent')
        expect(createUser).not.toHaveBeenCalled()

        createUser.mockRejectedValueOnce(new Error('create failed'))
        const failedWrapper = mount(UserForm, {
            props: { groups: [baseGroup] },
            global: { stubs: globalStubs, mocks: { $t: (key: string) => key } }
        })
        await failedWrapper.get('form').trigger('submit.prevent')
        await flushPromises()
        expect(failedWrapper.emitted('success')).toBeFalsy()
    })

    test('binds and submits user password form model', async () => {
        const wrapper = mount(UserPasswordForm, {
            props: {
                modelValue: {
                    password: 'initial',
                    passwordc: 'initial'
                }
            },
            global: {
                stubs: globalStubs,
                mocks: { $t: (key: string) => key }
            }
        })

        await wrapper.get('input[data-test="password"]').setValue('new-password')
        await wrapper.get('input[data-test="passwordc"]').setValue('new-password')
        await wrapper.get('form').trigger('submit.prevent')

        expect(wrapper.emitted('submit')).toHaveLength(1)
        expect(wrapper.get('input[data-test="password"]').element).toHaveProperty(
            'value',
            'new-password'
        )
    })
})
