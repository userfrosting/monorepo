<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Fortress\Adapter;

use UnhandledMatchError;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\I18n\Translator;

/**
 * Loads validation rules from a schema and generates client-side rules
 * compatible with the jQuery Validation (http://http://jqueryvalidation.org)
 * JS plugin.
 *
 * Generate jQuery Validation compatible rules from the specified
 * RequestSchema, as a JSON document. See url below as an example of what
 * this function will generate.
 *
 * @see https://github.com/jzaefferer/jquery-validation/blob/master/demo/bootstrap/index.html#L168-L209
 *
 * Returns the rules as an array.
 */
final class JqueryValidationArrayAdapter implements ValidationAdapterInterface
{
    /**
     * @param Translator $translator A Translator to be used to translate message ids found in the schema.
     */
    public function __construct(protected Translator $translator)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param string $arrayPrefix (Default: '')
     *
     * @return array{rules: mixed[], messages: mixed[]}
     */
    public function rules(RequestSchemaInterface $schema, string $arrayPrefix = ''): array
    {
        // Return containers
        $rules = [];
        $messages = [];

        // Loop through each field from the schema
        foreach ($schema->all() as $fieldName => $field) {
            // Apply prefix to field name
            $fieldNamePrefixed = ($arrayPrefix !== '') ? $arrayPrefix . '[' . $fieldName . ']' : $fieldName;

            // Build default container for field name
            $rules[$fieldNamePrefixed] = [];

            // If field has validators, loop through them
            if (isset($field['validators'])) {
                foreach ($field['validators'] as $validatorName => $validator) {
                    $validator = $validator ?? [];

                    // Skip messages that are for server-side use only
                    if (isset($validator['domain']) && $validator['domain'] == 'server') {
                        continue;
                    }

                    $newRules = $this->transformValidator($fieldNamePrefixed, $validatorName, $validator);
                    $rules[$fieldNamePrefixed] = array_merge($rules[$fieldNamePrefixed], $newRules);

                    // Message
                    if (isset($validator['message'])) {
                        $validator = array_merge(['self' => $fieldName], $validator);
                        if (!isset($messages[$fieldNamePrefixed])) {
                            $messages[$fieldNamePrefixed] = [];
                        }

                        // Copy the translated message to every translated rule created by this validation rule
                        $message = $this->translator->translate($validator['message'], $validator);
                        foreach ($newRules as $translatedRuleName => $rule) {
                            $messages[$fieldNamePrefixed][$translatedRuleName] = $message;
                        }
                    }
                }
            }
        }

        $result = [
            'rules'    => $rules,
            'messages' => $messages,
        ];

        return $result;
    }

    /**
     * Transform a validator for a particular field into one or more jQueryValidation rules.
     *
     * @param string   $fieldName
     * @param string   $validatorName
     * @param string[] $validator
     *
     * @return mixed[]
     */
    protected function transformValidator(string $fieldName, string $validatorName, array $validator): array
    {
        $transformedValidator = [];

        try {
            $transformed = match ($validatorName) {
                'email'                  => $this->transformGeneric('email'),
                'equals'                 => $this->transformEquals($validator),
                'integer'                => $this->transformGeneric('digits'),
                'length'                 => $this->transformLength($validator),
                'matches'                => $this->transformConditional('matchFormField', $validator, 'field'),
                'member_of'              => $this->transformConditional('memberOf', $validator, 'values'),
                'no_leading_whitespace'  => $this->transformGeneric('noLeadingWhitespace'),
                'no_trailing_whitespace' => $this->transformGeneric('noTrailingWhitespace'),
                'not_equals'             => $this->transformEquals($validator, label: 'notEquals'),
                'not_matches'            => $this->transformConditional('notMatchFormField', $validator, 'field'),
                'not_member_of'          => $this->transformConditional('notMemberOf', $validator, 'values'),
                'numeric'                => $this->transformGeneric('number'),
                'range'                  => $this->transformRange($validator),
                'regex'                  => $this->transformConditional('pattern', $validator, 'regex'),
                'required'               => $this->transformGeneric('required'),
                'telephone'              => $this->transformGeneric('phoneUS'),
                'uri'                    => $this->transformGeneric('url'),
                'username'               => $this->transformGeneric('username'),
            };
        } catch (UnhandledMatchError $e) {
            return [];
        }

        // If match didn't return anything, return empty array
        if ($transformed === []) {
            return [];
        }

        // Assign result of transformation to $transformedValidator
        list($label, $params) = $transformed;
        $transformedValidator[$label] = $params;

        return $transformedValidator;
    }

    /**
     * Transform a generic validator that doesn't require to mutate the params.
     *
     * @param string $label
     * @param mixed  $params
     *
     * @return array{string, mixed}
     */
    private function transformGeneric(string $label, mixed $params = true): array
    {
        return [$label, $params];
    }

    /**
     * @param string   $label
     * @param string[] $validator
     * @param string   $condition
     *
     * @return array{string, mixed}|array{}
     */
    private function transformConditional(string $label, array $validator, string $condition = 'value'): array
    {
        if (!isset($validator[$condition])) {
            return [];
        }

        return [$label, $validator[$condition]];
    }

    /**
     * @param string[] $validator
     * @param string   $label     (default 'equals')
     * @param string   $condition (default 'value')
     *
     * @return array{string, string[]}|array{}
     */
    private function transformEquals(array $validator, string $label = 'equals', string $condition = 'value'): array
    {
        if (!isset($validator[$condition])) {
            return [];
        }

        return [$label, $validator];
    }

    /**
     * @param string[] $validator
     *
     * @return array{string, string|string[]}
     */
    private function transformLength(array $validator): array
    {
        if (isset($validator['min']) && isset($validator['max'])) {
            return ['rangelength', [$validator['min'], $validator['max']]];
        } elseif (isset($validator['min'])) {
            return ['minlength', $validator['min']];
        } else {
            return ['maxlength', $validator['max']];
        }
    }

    /**
     * @param string[] $validator
     *
     * @return array{string, string|string[]}
     */
    private function transformRange(array $validator): array
    {
        if (isset($validator['min']) && isset($validator['max'])) {
            return ['range', [$validator['min'], $validator['max']]];
        } elseif (isset($validator['min'])) {
            return ['min', $validator['min']];
        } else {
            return ['max', $validator['max']];
        }
    }
}
