import { parse as YamlParse } from 'yaml'
import {
    withMessage,
    required,
    maxLength,
    minLength,
    email,
    integer,
    numeric,
    url,
    oneOf,
    between,
    regex,
    not
} from '@regle/rules'
import { useTranslator } from '../stores'

export function useRuleSchemaAdapter() {
    /**
     * Parse the YAML schema string into a JavaScript object.
     *
     * @param rawSchema The YAML schema string to parse.
     * @returns RuleSchema The Regle schema object.
     */
    function adapt(rawSchema: string) {
        // The YAML data parsed to a JavaScript object
        const sourceSchema = parse(rawSchema)

        // The Regle schema object to be returned
        const regleSchema: any = {}

        // Iterate over each field in the schema
        for (const field in sourceSchema) {
            // Check if the field is a direct property of the sourceSchema object
            if (Object.prototype.hasOwnProperty.call(sourceSchema, field)) {
                // Get the field rules from the source schema
                const schemaFieldRules = sourceSchema[field]?.validators || {}

                // The returned regle rules for the field
                const regleRules: Record<string, any> = {}

                // Iterate over each rule in the source schema field rules
                for (const key of Object.keys(schemaFieldRules)) {
                    adaptRule(key, schemaFieldRules, regleRules)
                }

                regleSchema[field] = regleRules
            }
        }

        return regleSchema
    }

    /**
     * Parse the YAML schema string into a JavaScript object.
     *
     * @param rawSchema The YAML schema string to parse.
     * @returns The parsed YAML schema as a JavaScript object.
     */
    function parse(rawSchema: string): Record<string, any> {
        return YamlParse(rawSchema)
    }

    function translateMessage(fieldRulesMeta: { message?: string; [key: string]: any }): string {
        const { translate } = useTranslator()

        // If there's no message, return an empty string
        if (!fieldRulesMeta.message) {
            return ''
        }

        // Copy the field rules meta to avoid mutation and remove the message key
        const fieldRulesMetaCopy = { ...fieldRulesMeta }
        delete fieldRulesMetaCopy.message

        return translate(fieldRulesMeta.message, fieldRulesMetaCopy)
    }

    function adaptRule(key: string, schemaFieldRules: any, regleRules: Record<string, any>) {
        // Required
        if (key === 'required' && schemaFieldRules.required) {
            const message: string = translateMessage(schemaFieldRules.required)
            regleRules['required'] = message === '' ? required : withMessage(required, message)
        }

        // Email
        if (key === 'email' && schemaFieldRules.email) {
            const message: string = translateMessage(schemaFieldRules.email)
            regleRules['email'] = withMessage(email, message)
        }

        // Length
        if (key === 'length' && schemaFieldRules.length) {
            if (schemaFieldRules.length.min !== undefined) {
                const message: string = translateMessage(schemaFieldRules.length)
                regleRules['minLength'] =
                    message === ''
                        ? minLength(schemaFieldRules.length.min)
                        : withMessage(minLength(schemaFieldRules.length.min), message)
            }
            if (schemaFieldRules.length.max !== undefined) {
                const message: string = translateMessage(schemaFieldRules.length)
                regleRules['maxLength'] =
                    message === ''
                        ? maxLength(schemaFieldRules.length.max)
                        : withMessage(maxLength(schemaFieldRules.length.max), message)
            }
        }

        // TODO : matches
        if (key === 'matches' && schemaFieldRules.matches) {
            console.warn('Validation rule "matches" not implemented yet')
            // console.debug('Matched rule: matches', schemaFieldRules.matches)
            // sameAs: sameAs(() => form.value.password),
        }

        // TODO : equals
        if (key === 'equals' && schemaFieldRules.equals) {
            console.warn('Validation rule "equals" not implemented yet')
        }

        // Integer
        if (key === 'integer' && schemaFieldRules.integer) {
            const message: string = translateMessage(schemaFieldRules.integer)
            regleRules['integer'] = message === '' ? integer : withMessage(integer, message)
        }

        // member_of
        if (key === 'member_of' && schemaFieldRules.member_of) {
            const message: string = translateMessage(schemaFieldRules.member_of)
            regleRules['member_of'] =
                message === ''
                    ? oneOf(schemaFieldRules.member_of.values)
                    : withMessage(oneOf(schemaFieldRules.member_of.values), message)
        }

        // no_leading_whitespace
        if (key === 'no_leading_whitespace' && schemaFieldRules.no_leading_whitespace) {
            const message: string = translateMessage(schemaFieldRules.no_leading_whitespace)
            regleRules['no_leading_whitespace'] =
                message === '' ? regex(/^\S.*$/) : withMessage(regex(/^\S.*$/), message)
        }

        // no_trailing_whitespace
        if (key === 'no_trailing_whitespace' && schemaFieldRules.no_trailing_whitespace) {
            const message: string = translateMessage(schemaFieldRules.no_trailing_whitespace)
            regleRules['no_trailing_whitespace'] =
                message === '' ? regex(/^.*\S$/) : withMessage(regex(/^.*\S$/), message)
        }

        // TODO : not_equals
        if (key === 'not_equals' && schemaFieldRules.not_equals) {
            console.warn('Validation rule "not_equals" not implemented yet')
        }

        // TODO : not_matches
        if (key === 'not_matches' && schemaFieldRules.not_matches) {
            console.warn('Validation rule "not_matches" not implemented yet')
        }

        // not_member_of
        if (key === 'not_member_of' && schemaFieldRules.not_member_of) {
            const message: string = translateMessage(schemaFieldRules.not_member_of)
            regleRules['not_member_of'] =
                message === ''
                    ? not(oneOf(schemaFieldRules.not_member_of.values))
                    : withMessage(not(oneOf(schemaFieldRules.not_member_of.values)), message)
        }

        // Numeric
        if (key === 'numeric' && schemaFieldRules.numeric) {
            const message: string = translateMessage(schemaFieldRules.numeric)
            regleRules['numeric'] = message === '' ? numeric : withMessage(numeric, message)
        }

        // Range
        if (key === 'range' && schemaFieldRules.range) {
            const message: string = translateMessage(schemaFieldRules.range)
            regleRules['range'] =
                message === ''
                    ? between(schemaFieldRules.range.min, schemaFieldRules.range.max)
                    : withMessage(
                          between(schemaFieldRules.range.min, schemaFieldRules.range.max),
                          message
                      )
        }

        // Regex
        if (key === 'regex' && schemaFieldRules.regex) {
            const message: string = translateMessage(schemaFieldRules.regex)
            regleRules['regex'] =
                message === ''
                    ? regex(new RegExp(schemaFieldRules.regex.regex))
                    : withMessage(regex(new RegExp(schemaFieldRules.regex.regex)), message)
        }

        // TODO : telephone
        if (key === 'telephone' && schemaFieldRules.telephone) {
            console.warn('Validation rule "telephone" not implemented yet')
        }

        // uri
        if (key === 'uri' && schemaFieldRules.uri) {
            const message: string = translateMessage(schemaFieldRules.uri)
            regleRules['uri'] = message === '' ? url : withMessage(url, message)
        }

        // Username
        if (key === 'username' && schemaFieldRules.username) {
            const message: string = translateMessage(schemaFieldRules.username)
            regleRules['username'] =
                message === ''
                    ? regex(/^([a-z0-9.\-_])+$/i)
                    : withMessage(regex(/^([a-z0-9.\-_])+$/i), message)
        }
    }

    return { adapt, parse, translateMessage }
}
