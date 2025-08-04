import { describe, test, expect, vi, afterEach } from 'vitest'
import { useRuleSchemaAdapter } from '../../composables/useRuleSchemaAdapter'
import { useRegle } from '@regle/core'

// Mock the translator store
const translateMock = vi.fn((key: string) => key || '')
vi.mock('@userfrosting/sprinkle-core/stores', () => ({
    useTranslator: () => ({
        translate: translateMock
    })
}))

describe('useRuleSchemaAdapter', () => {
    afterEach(() => {
        vi.clearAllMocks()
    })

    test('should parse a basic schema', () => {
        const yamlInput = `
            foo:
                validators:
                    length:
                        min: 1
                        max: 132
                    required: true
        `

        const { r$ } = useRegle(
            {
                foo: ''
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        expect(r$.foo).toBeDefined()
        expect(r$.foo.$rules.required).toBeDefined()
        expect(r$.foo.$rules.minLength).toBeDefined()
        expect(r$.foo.$rules.maxLength).toBeDefined()
    })

    test('should parse a schema with a custom message', () => {
        const yamlInput = `
            name:
                validators:
                    length:
                        min: 2
                        max: 20
                    required: true
            email:
                validators:
                    length:
                        min: 1
                        max: 30
                    email: true
        `

        const { r$ } = useRegle(
            {
                name: '',
                email: ''
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        expect(r$.name).toBeDefined()
        expect(r$.name.$rules.required).toBeDefined()
        expect(r$.name.$rules.minLength).toBeDefined()
        expect(r$.name.$rules.maxLength).toBeDefined()
        expect(r$.email).toBeDefined()
        expect(r$.email.$rules.required).not.toBeDefined()
        expect(r$.email.$rules.minLength).toBeDefined()
        expect(r$.email.$rules.maxLength).toBeDefined()
    })

    test('should parse a basic schema', () => {
        const yamlInput = `
            first_name:
                validators:
                    required:
                        label: "&FIRST_NAME"
                        message: VALIDATE.REQUIRED
            last_name:
                validators:
                    required: true
        `

        const { r$ } = useRegle(
            {
                first_name: '',
                last_name: ''
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.REQUIRED', {
            label: '&FIRST_NAME'
        })

        expect(r$.first_name.$rules.required.$message).toBe('VALIDATE.REQUIRED') // Custom message
        expect(r$.last_name.$rules.required.$message).toBe('This field is required') // Default message
    })
})
