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
use UserFrosting\Fortress\FortressException;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;

/**
 * Loads validation rules from a schema and generates client-side rules
 * compatible with the FormValidation (http://formvalidation.io) JS plugin.
 *
 * Generate FormValidation compatible rules from the specified RequestSchema,
 * as HTML5 `data-*` attributes. See "Setting validator options via HTML
 * attributes" (http://formvalidation.io/examples/attribute/) as an example of
 * what this function will generate.
 */
final class FormValidationHtml5Adapter implements ValidationAdapterInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array<string> Returns an array of rules, mapping field names -> string of data-* attributes, separated by spaces.
     *                       Example: `data-fv-notempty data-fv-notempty-message="The gender is required"`.
     */
    public function rules(RequestSchemaInterface $schema): array
    {
        // Return container
        $rules = [];

        // Loop through each field from the schema
        foreach ($schema->all() as $fieldName => $field) {
            // Build default container for field name
            $fieldRules = [];

            // If field has validators, loop through them
            if (isset($field['validators'])) {
                foreach ($field['validators'] as $validatorName => $validator) {
                    $validator = $validator ?? [];

                    // Skip messages that are for server-side use only
                    if (isset($validator['domain']) && $validator['domain'] === 'server') {
                        continue;
                    }

                    $fieldRules[] = $this->transformValidator($fieldName, $validatorName, $validator);
                }
            }

            $rules[$fieldName] = implode('', $fieldRules);
        }

        return $rules;
    }

    /**
     * Transform a validator for a particular field into one or more FormValidation rules.
     *
     * @param string   $fieldName     The form field name.
     * @param string   $validatorName The validator name.
     * @param string[] $validator     The validator parameters.
     *
     * @return string
     */
    protected function transformValidator(string $fieldName, string $validatorName, array $validator): string
    {
        // Match validator name to transform method
        try {
            $fieldRules = match ($validatorName) {
                'required' => $this->html5Attributes($validator, 'data-fv-notempty'),
                'length'   => $this->transformLength($validator),
                'range'    => $this->transformRange($validator),
                'integer'  => $this->html5Attributes($validator, 'data-fv-integer'),
                'array'    => $this->transformArray($validator),
                'email'    => $this->html5Attributes($validator, 'data-fv-emailaddress'),
                'matches'  => $this->transformMatch($validator),
            };
        } catch (UnhandledMatchError $e) {
            return '';
        }

        return $fieldRules;
    }

    /**
     * @param mixed[] $validator
     *
     * @return string
     */
    private function transformLength(array $validator): string
    {
        $prefix = 'data-fv-stringlength';
        $fieldRules = $this->html5Attributes($validator, $prefix);

        if (isset($validator['min'])) {
            $fieldRules .= "$prefix-min={$validator['min']} ";
        }

        if (isset($validator['max'])) {
            $fieldRules .= "$prefix-max={$validator['max']} ";
        }

        return $fieldRules;
    }

    /**
     * @param mixed[] $validator
     *
     * @return string
     */
    private function transformRange(array $validator): string
    {
        if (isset($validator['min']) && isset($validator['max'])) {
            $prefix = 'data-fv-between';
            $fieldRules = $this->html5Attributes($validator, $prefix);
            $fieldRules .= "$prefix-min={$validator['min']} ";
            $fieldRules .= "$prefix-max={$validator['max']} ";

            return $fieldRules;
        }

        if (isset($validator['min'])) {
            $prefix = 'data-fv-greaterthan';
            $fieldRules = $this->html5Attributes($validator, $prefix);
            $fieldRules .= "$prefix-value={$validator['min']} ";

            return $fieldRules;
        }

        if (isset($validator['max'])) {
            $prefix = 'data-fv-lessthan';
            $fieldRules = $this->html5Attributes($validator, $prefix);
            $fieldRules .= "$prefix-value={$validator['max']} ";

            return $fieldRules;
        }

        return '';
    }

    /**
     * @param mixed[] $validator
     *
     * @return string
     */
    private function transformArray(array $validator): string
    {
        $prefix = 'data-fv-choice';
        $fieldRules = $this->html5Attributes($validator, $prefix);

        if (isset($validator['min'])) {
            $fieldRules .= "$prefix-min={$validator['min']} ";
        }
        if (isset($validator['max'])) {
            $fieldRules .= "$prefix-max={$validator['max']} ";
        }

        return $fieldRules;
    }

    /**
     * @param mixed[] $validator
     *
     * @return string
     */
    private function transformMatch(array $validator): string
    {
        if (!isset($validator['field'])) {
            throw new FortressException('Match validator must have a field parameter');
        }

        $prefix = 'data-fv-identical';
        $fieldRules = $this->html5Attributes($validator, $prefix);
        $fieldRules .= "$prefix-field={$validator['field']} ";

        return $fieldRules;
    }

    /**
     * Transform a validator for a particular field into a string of
     * FormValidation rules as HTML data-* attributes.
     *
     * @param string[] $validator
     * @param string   $prefix
     *
     * @return string
     */
    private function html5Attributes(array $validator, string $prefix): string
    {
        $attr = "$prefix=true ";

        if (isset($validator['message'])) {
            $msg = $validator['message'];
            $attr .= "$prefix-message=\"$msg\" ";
        }

        return $attr;
    }
}
