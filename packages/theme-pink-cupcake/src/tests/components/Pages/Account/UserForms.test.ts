import { describe, expect, test, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import FormUserEmail from '../../../../components/Pages/Account/FormUserEmail.vue'
import FormUserPassword from '../../../../components/Pages/Account/FormUserPassword.vue'
import FormUserProfile from '../../../../components/Pages/Account/FormUserProfile.vue'

const check = vi.fn()
const submitEmailEdit = vi.fn().mockResolvedValue(undefined)
const submitPasswordEdit = vi.fn().mockResolvedValue(undefined)
const submitProfileEdit = vi.fn().mockResolvedValue(undefined)
const emailFormData = ref({ email: '', passwordcheck: '' })
const passwordFormData = ref({ passwordcheck: '', password: '', passwordc: '' })
const profileFormData = ref({ first_name: '', last_name: '', locale: '' })

vi.mock('@userfrosting/sprinkle-account/stores', () => ({
    useAuthStore: () => ({
        user: {
            email: 'jane@example.com',
            first_name: 'Jane',
            last_name: 'Doe',
            locale: 'en_US'
        },
        check
    })
}))

vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useConfigStore: () => ({
        get: vi.fn().mockReturnValue({ en_US: 'English', fr_CA: 'French' })
    })
}))

vi.mock('@userfrosting/sprinkle-account/composables', () => ({
    useUserEmailEditApi: () => ({
        submitEmailEdit,
        r$: {
            $error: false,
            email: { $error: false },
            passwordcheck: { $error: false },
            $errors: { email: [], passwordcheck: [] }
        },
        formData: emailFormData,
        apiLoading: ref(false)
    }),
    useUserPasswordEditApi: () => ({
        submitPasswordEdit,
        r$: {
            $error: false,
            $errors: { password: [], passwordc: [], passwordcheck: [] }
        },
        formData: passwordFormData,
        apiLoading: ref(false),
        minLength: 8,
        maxLength: 64
    }),
    useUserProfileEditApi: () => ({
        submitProfileEdit,
        r$: {
            $error: false,
            first_name: { $error: false },
            last_name: { $error: false },
            $errors: { first_name: [], last_name: [], locale: [] }
        },
        formData: profileFormData,
        apiLoading: ref(false)
    })
}))

describe('account user forms', () => {
    beforeEach(() => {
        check.mockClear()
        submitEmailEdit.mockClear()
        submitPasswordEdit.mockClear()
        submitProfileEdit.mockClear()
        emailFormData.value = { email: '', passwordcheck: '' }
        passwordFormData.value = { passwordcheck: '', password: '', passwordc: '' }
        profileFormData.value = { first_name: '', last_name: '', locale: '' }
    })

    test('FormUserEmail initializes from auth user and resets password check after submit', async () => {
        const wrapper = mount(FormUserEmail, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: ['UFFormValidationError', 'font-awesome-icon']
            }
        })

        expect(emailFormData.value.email).toBe('jane@example.com')
        await wrapper.get('input[data-test="passwordcheck"]').setValue('secret')
        await wrapper.get('form').trigger('submit.prevent')

        expect(submitEmailEdit).toHaveBeenCalled()
        await vi.waitFor(() => {
            expect(emailFormData.value.passwordcheck).toBe('')
            expect(check).toHaveBeenCalled()
        })
    })

    test('FormUserPassword submits and resets fields on success', async () => {
        const wrapper = mount(FormUserPassword, {
            global: {
                mocks: { $t: (key: string, params?: any) => (params ? `${key}` : key) },
                stubs: ['UFFormValidationError', 'font-awesome-icon']
            }
        })

        await wrapper.get('input[data-test="password"]').setValue('new-secret')
        await wrapper.get('input[data-test="passwordc"]').setValue('new-secret')
        await wrapper.get('input[data-test="passwordcheck"]').setValue('old-secret')
        await wrapper.get('form').trigger('submit.prevent')

        expect(submitPasswordEdit).toHaveBeenCalledWith({
            passwordcheck: 'old-secret',
            password: 'new-secret',
            passwordc: 'new-secret'
        })
        await vi.waitFor(() => {
            expect(passwordFormData.value).toEqual({
                passwordcheck: '',
                password: '',
                passwordc: ''
            })
            expect(check).toHaveBeenCalled()
        })
    })

    test('FormUserProfile initializes from auth user, shows locales, and submits', async () => {
        const wrapper = mount(FormUserProfile, {
            global: {
                mocks: { $t: (key: string) => key },
                stubs: ['UFFormValidationError', 'font-awesome-icon']
            }
        })

        expect(profileFormData.value).toEqual({
            first_name: 'Jane',
            last_name: 'Doe',
            locale: 'en_US'
        })
        expect(wrapper.findAll('option')).toHaveLength(2)

        await wrapper.get('input[data-test="first_name"]').setValue('Janet')
        await wrapper.get('form').trigger('submit.prevent')

        expect(submitProfileEdit).toHaveBeenCalledWith({
            first_name: 'Janet',
            last_name: 'Doe',
            locale: 'en_US'
        })
        await vi.waitFor(() => {
            expect(check).toHaveBeenCalled()
        })
    })
})
