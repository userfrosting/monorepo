import { describe, test, expect, vi, afterEach, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { defineComponent } from 'vue'
import { mount } from '@vue/test-utils'
import { useRuleSchemaAdapter } from '../../composables/useRuleSchemaAdapter'
import { useRegle } from '@regle/core'

// Mock the translator store
const translateMock = vi.fn((key: string) => key || '')
vi.mock('../../stores', () => ({
    useTranslator: () => ({
        translate: translateMock
    })
}))

const createRegle = (formData: Record<string, any>, schema: Record<string, any>) => {
    let regle: any = null

    const TestHarness = defineComponent({
        setup() {
            regle = useRegle(formData, schema)
            return () => null
        }
    })

    mount(TestHarness)

    if (regle === null) {
        throw new Error('Failed to initialize Regle test harness')
    }

    return regle
}

describe('useRuleSchemaAdapter', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    afterEach(() => {
        vi.clearAllMocks()
    })

    test('should parse a basic schema', () => {
        const yamlInput = {
            foo: {
                validators: {
                    length: {
                        min: 1,
                        max: 132
                    },
                    required: true
                }
            }
        }

        const { r$ } = createRegle(
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
        const yamlInput = {
            name: {
                validators: {
                    length: {
                        min: 2,
                        max: 20
                    },
                    required: true
                }
            },
            email: {
                validators: {
                    length: {
                        min: 1,
                        max: 30
                    },
                    email: true
                }
            }
        }

        const { r$ } = createRegle(
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

    test('should parse a bad schema without errors', () => {
        // N.B.: The schema is missing the 'validators' key
        const yamlInput = {
            foo: {
                length: {
                    min: 1,
                    max: 132
                },
                required: true
            }
        }

        const { r$ } = createRegle(
            {
                foo: ''
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        expect(r$.foo).toBeDefined()
        expect(r$.foo.$rules.required).not.toBeDefined()
        expect(r$.foo.$rules.minLength).not.toBeDefined()
        expect(r$.foo.$rules.maxLength).not.toBeDefined()
    })

    test('required rule', () => {
        const yamlInput = {
            first_name: {
                validators: {
                    required: {
                        label: '&FIRST_NAME',
                        message: 'VALIDATE.REQUIRED'
                    }
                }
            },
            last_name: {
                validators: {
                    required: true
                }
            }
        }

        const { r$ } = createRegle(
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

    test('email rule', () => {
        const yamlInput = {
            email: {
                validators: {
                    email: {
                        message: 'VALIDATE.INVALID_EMAIL'
                    }
                }
            },
            email2: {
                validators: {
                    email: true
                }
            }
        }

        const { r$ } = createRegle(
            {
                email: '',
                email2: ''
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.INVALID_EMAIL', {})

        expect(r$.email.$rules.email.$message).toBe('VALIDATE.INVALID_EMAIL') // Custom message
        expect(r$.email2.$rules.email.$message).toBe('This field is not valid') // Default message
    })

    test('length rule', () => {
        const yamlInput = {
            tooShort: {
                validators: {
                    length: {
                        min: 5
                    }
                }
            },
            tooLong: {
                validators: {
                    length: {
                        max: 2
                    }
                }
            },
            tooShortWithMessage: {
                validators: {
                    length: {
                        min: 5,
                        message: 'VALIDATE.LENGTH_RANGE'
                    }
                }
            },
            tooLongWithMessage: {
                validators: {
                    length: {
                        max: 2,
                        message: 'VALIDATE.LENGTH_RANGE'
                    }
                }
            }
        }

        const { r$ } = createRegle(
            {
                tooShort: '1',
                tooLong: '123',
                tooShortWithMessage: '1',
                tooLongWithMessage: '123'
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenNthCalledWith(1, 'VALIDATE.LENGTH_RANGE', { min: 5 })
        expect(translateMock).toHaveBeenNthCalledWith(2, 'VALIDATE.LENGTH_RANGE', { max: 2 })

        expect(r$.tooShort.$silentErrors).toEqual(['The value must be at least 5 characters long']) // Custom message
        expect(r$.tooLong.$silentErrors).toEqual(['The value must be at most 2 characters long']) // Default message
        expect(r$.tooShortWithMessage.$silentErrors).toEqual(['VALIDATE.LENGTH_RANGE']) // Custom message
        expect(r$.tooLongWithMessage.$silentErrors).toEqual(['VALIDATE.LENGTH_RANGE']) // Default message
    })

    test('integer rule', () => {
        const yamlInput = {
            foo: {
                validators: {
                    integer: true
                }
            },
            bar: {
                validators: {
                    integer: {
                        message: 'VALIDATE.INVALID_INTEGER'
                    }
                }
            },
            foobar: {
                validators: {
                    integer: true
                }
            }
        }

        const { r$ } = createRegle(
            {
                foo: 'one',
                bar: 'two',
                foobar: 92
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.INVALID_INTEGER', {})

        expect(r$.foo.$silentErrors).toEqual(['The value must be an integer']) // Custom message
        expect(r$.bar.$silentErrors).toEqual(['VALIDATE.INVALID_INTEGER']) // Default message
        expect(r$.foobar.$silentErrors).toEqual([]) // Valid
    })

    test('member_of rule', () => {
        const yamlInput = {
            genus: {
                validators: {
                    member_of: {
                        values: ['Megascops', 'Bubo', 'Glaucidium', 'Tyto', 'Athene'],
                        message: 'Sorry, that is not one of the permitted genuses.'
                    }
                }
            },
            owls: {
                validators: {
                    member_of: {
                        values: ['Foo', 'Bar']
                    }
                }
            },
            valid: {
                validators: {
                    member_of: {
                        values: ['Foo', 'Bar']
                    }
                }
            }
        }

        const { r$ } = createRegle(
            {
                genus: 'Foo',
                owls: 'Hedwig',
                valid: 'Foo'
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith(
            'Sorry, that is not one of the permitted genuses.',
            {
                values: ['Megascops', 'Bubo', 'Glaucidium', 'Tyto', 'Athene']
            }
        )

        expect(r$.genus.$silentErrors).toEqual(['Sorry, that is not one of the permitted genuses.']) // Custom message
        expect(r$.owls.$silentErrors).toEqual(['The value must be one of the following: Foo, Bar']) // Default message
        expect(r$.valid.$silentErrors).toEqual([]) // Valid
    })

    test('not_member_of rule', () => {
        const yamlInput = {
            genus: {
                validators: {
                    not_member_of: {
                        values: ['Megascops', 'Bubo', 'Glaucidium', 'Tyto', 'Athene'],
                        message: 'VALIDATE.NOT_MEMBER_OF'
                    }
                }
            },
            owls: {
                validators: {
                    not_member_of: {
                        values: ['Foo', 'Bar']
                    }
                }
            },
            valid: {
                validators: {
                    not_member_of: {
                        values: ['Foo', 'Bar']
                    }
                }
            }
        }

        const { r$ } = createRegle(
            {
                genus: 'Megascops',
                owls: 'Foo',
                valid: 'Hedwig'
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.NOT_MEMBER_OF', {
            values: ['Megascops', 'Bubo', 'Glaucidium', 'Tyto', 'Athene']
        })

        expect(r$.genus.$silentErrors).toEqual(['VALIDATE.NOT_MEMBER_OF']) // Custom message
        expect(r$.owls.$silentErrors).toEqual(['Error']) // Default message
        expect(r$.valid.$silentErrors).toEqual([]) // Valid
    })

    test('no_leading_whitespace rule', () => {
        const yamlInput = {
            withMessage: {
                validators: {
                    no_leading_whitespace: {
                        label: '&USERNAME',
                        message: 'VALIDATE.NO_LEAD_WS'
                    }
                }
            },
            defaultMessage: {
                validators: {
                    no_leading_whitespace: true
                }
            },
            valid: {
                validators: {
                    no_leading_whitespace: true
                }
            }
        }

        const { r$ } = createRegle(
            {
                withMessage: ' Foo',
                defaultMessage: ' Foo',
                valid: 'Foo'
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.NO_LEAD_WS', {
            label: '&USERNAME'
        })

        expect(r$.withMessage.$silentErrors).toEqual(['VALIDATE.NO_LEAD_WS']) // Custom message
        expect(r$.defaultMessage.$silentErrors).toEqual([
            'The value must match the required pattern'
        ]) // Default message
        expect(r$.valid.$silentErrors).toEqual([]) // Valid
    })

    test('no_trailing_whitespace rule', () => {
        const yamlInput = {
            withMessage: {
                validators: {
                    no_trailing_whitespace: {
                        label: '&USERNAME',
                        message: 'VALIDATE.NO_TRAIL_WS'
                    }
                }
            },
            defaultMessage: {
                validators: {
                    no_trailing_whitespace: true
                }
            },
            valid: {
                validators: {
                    no_trailing_whitespace: true
                }
            }
        }

        const { r$ } = createRegle(
            {
                withMessage: 'Foo ',
                defaultMessage: 'Foo ',
                valid: 'Foo'
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.NO_TRAIL_WS', {
            label: '&USERNAME'
        })

        expect(r$.withMessage.$silentErrors).toEqual(['VALIDATE.NO_TRAIL_WS']) // Custom message
        expect(r$.defaultMessage.$silentErrors).toEqual([
            'The value must match the required pattern'
        ]) // Default message
        expect(r$.valid.$silentErrors).toEqual([]) // Valid
    })

    test('numeric rule', () => {
        const yamlInput = {
            withMessage: {
                validators: {
                    numeric: {
                        message: 'VALIDATE.INVALID_NUMERIC'
                    }
                }
            },
            defaultMessage: {
                validators: {
                    numeric: true
                }
            },
            valid: {
                validators: {
                    numeric: true
                }
            }
        }

        const { r$ } = createRegle(
            {
                withMessage: 'Foo',
                defaultMessage: 'Foo',
                valid: '10.2'
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.INVALID_NUMERIC', {})

        expect(r$.withMessage.$silentErrors).toEqual(['VALIDATE.INVALID_NUMERIC']) // Custom message
        expect(r$.defaultMessage.$silentErrors).toEqual(['The value must be numeric']) // Default message
        expect(r$.valid.$silentErrors).toEqual([]) // Valid
    })

    test('range rule', () => {
        const yamlInput = {
            withMessage: {
                validators: {
                    range: {
                        min: 0,
                        max: 10,
                        message: 'VALIDATE.INVALID_RANGE'
                    }
                }
            },
            defaultMessage: {
                validators: {
                    range: {
                        min: 0,
                        max: 10
                    }
                }
            },
            valid: {
                validators: {
                    range: {
                        min: 0,
                        max: 10
                    }
                }
            }
        }

        const { r$ } = createRegle(
            {
                withMessage: 92,
                defaultMessage: 92,
                valid: 9
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.INVALID_RANGE', {
            min: 0,
            max: 10
        })

        expect(r$.withMessage.$silentErrors).toEqual(['VALIDATE.INVALID_RANGE']) // Custom message
        expect(r$.defaultMessage.$silentErrors).toEqual(['The value must be between 0 and 10']) // Default message
        expect(r$.valid.$silentErrors).toEqual([]) // Valid
    })

    test('regex rule', () => {
        const yamlInput = {
            withMessage: {
                validators: {
                    regex: {
                        regex: '^who(o*)$',
                        message: 'VALIDATE.INVALID_VALUE'
                    }
                }
            },
            defaultMessage: {
                validators: {
                    regex: {
                        regex: '^who(o*)$'
                    }
                }
            },
            valid: {
                validators: {
                    regex: {
                        regex: '^who(o*)$'
                    }
                }
            }
        }

        const { r$ } = createRegle(
            {
                withMessage: 'hum',
                defaultMessage: 'hello',
                valid: 'whooo'
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.INVALID_VALUE', {
            regex: '^who(o*)$'
        })

        expect(r$.withMessage.$silentErrors).toEqual(['VALIDATE.INVALID_VALUE']) // Custom message
        expect(r$.defaultMessage.$silentErrors).toEqual([
            'The value must match the required pattern'
        ]) // Default message
        expect(r$.valid.$silentErrors).toEqual([]) // Valid
    })

    test('uri rule', () => {
        const yamlInput = {
            withMessage: {
                validators: {
                    uri: {
                        message: 'VALIDATE.INVALID_URL'
                    }
                }
            },
            defaultMessage: {
                validators: {
                    uri: true
                }
            },
            valid: {
                validators: {
                    uri: true
                }
            }
        }

        const { r$ } = createRegle(
            {
                withMessage: 'foo',
                defaultMessage: 'bar@example.com',
                valid: 'http://example.com'
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.INVALID_URL', {})

        expect(r$.withMessage.$silentErrors).toEqual(['VALIDATE.INVALID_URL']) // Custom message
        expect(r$.defaultMessage.$silentErrors).toEqual(['The value must be a valid URL']) // Default message
        expect(r$.valid.$silentErrors).toEqual([]) // Valid
    })

    test('username rule', () => {
        const yamlInput = {
            withMessage: {
                validators: {
                    username: {
                        message: 'VALIDATE.INVALID_USERNAME'
                    }
                }
            },
            defaultMessage: {
                validators: {
                    username: true
                }
            },
            valid: {
                validators: {
                    username: true
                }
            }
        }

        const { r$ } = createRegle(
            {
                withMessage: 'My Name',
                defaultMessage: 'bar@example.com',
                valid: 'foo.bar-bax_123'
            },
            useRuleSchemaAdapter().adapt(yamlInput)
        )

        // Set translator expectations
        expect(translateMock).toHaveBeenCalledExactlyOnceWith('VALIDATE.INVALID_USERNAME', {})

        expect(r$.withMessage.$silentErrors).toEqual(['VALIDATE.INVALID_USERNAME']) // Custom message
        expect(r$.defaultMessage.$silentErrors).toEqual([
            'The value must match the required pattern'
        ]) // Default message
        expect(r$.valid.$silentErrors).toEqual([]) // Valid
    })

    test('returns empty string when translateMessage has no message key', () => {
        const adapter = useRuleSchemaAdapter()
        expect(adapter.translateMessage({ label: '&USERNAME' })).toBe('')
    })

    test('warns for unimplemented rules', () => {
        const warnSpy = vi.spyOn(console, 'warn').mockImplementation(() => undefined)

        const yamlInput = {
            field: {
                validators: {
                    matches: true,
                    equals: true,
                    not_equals: true,
                    not_matches: true,
                    telephone: true
                }
            }
        }

        const adapted = useRuleSchemaAdapter().adapt(yamlInput)

        expect(adapted).toEqual({ field: {} })
        expect(warnSpy).toHaveBeenCalledTimes(5)
        expect(warnSpy).toHaveBeenNthCalledWith(1, 'Validation rule "matches" not implemented yet')
        expect(warnSpy).toHaveBeenNthCalledWith(2, 'Validation rule "equals" not implemented yet')
        expect(warnSpy).toHaveBeenNthCalledWith(
            3,
            'Validation rule "not_equals" not implemented yet'
        )
        expect(warnSpy).toHaveBeenNthCalledWith(
            4,
            'Validation rule "not_matches" not implemented yet'
        )
        expect(warnSpy).toHaveBeenNthCalledWith(
            5,
            'Validation rule "telephone" not implemented yet'
        )
    })

    test('ignores inherited schema fields', () => {
        const inheritedSchema = {
            inherited: {
                validators: {
                    required: true
                }
            }
        }

        const sourceSchema = Object.create(inheritedSchema)
        sourceSchema.own = {
            validators: {
                required: true
            }
        }

        const adapted = useRuleSchemaAdapter().adapt(sourceSchema)

        expect(adapted.inherited).toBeUndefined()
        expect(adapted.own).toBeDefined()
        expect(adapted.own.required).toBeDefined()
    })
})
