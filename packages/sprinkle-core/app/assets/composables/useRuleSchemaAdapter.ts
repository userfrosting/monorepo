import { parse as YamlParse } from 'yaml'
import { withMessage, required, maxLength, minLength, email } from '@regle/rules'
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

        // matches
        if (key === 'matches' && schemaFieldRules.matches) {
            console.warn('Validation rule "matches" not implemented yet')
            // console.debug('Matched rule: matches', schemaFieldRules.matches)
            // sameAs: sameAs(() => form.value.password),
        }

        // equals : TODO
        // integer : TODO
        // member_of : TODO
        // no_leading_whitespace : TODO
        // no_trailing_whitespace : TODO
        // not_equals : TODO
        // not_matches : TODO
        // not_member_of : TODO
        // numeric : TODO
        // range : TODO
        // regex : TODO
        // telephone : TODO
        // uri : TODO
        // username : TODO
    }

    return { adapt, parse, translateMessage }
}
