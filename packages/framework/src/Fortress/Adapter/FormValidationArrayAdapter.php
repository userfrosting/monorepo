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
 * compatible with the FormValidation (http://formvalidation.io) JS plugin.
 */
final class FormValidationArrayAdapter implements ValidationAdapterInterface
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
     * @return array<string, mixed[]>
     */
    public function rules(RequestSchemaInterface $schema): array
    {
        // Return container
        $rules = [];

        // Loop through each field from the schema
        foreach ($schema->all() as $fieldName => $field) {
            // Build default container for field name
            $rules[$fieldName] = [];
            $rules[$fieldName]['validators'] = [];

            // If field has validators, loop through them
            if (isset($field['validators'])) {
                foreach ($field['validators'] as $validatorName => $validator) {
                    $validator = $validator ?? [];
                    $rules[$fieldName]['validators'] = array_merge($rules[$fieldName]['validators'], $this->transformValidator($fieldName, $validatorName, $validator));
                }
            }
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
     * @return array<string, mixed[]>
     */
    protected function transformValidator(string $fieldName, string $validatorName, array $validator): array
    {
        $params = [];
        $transformedValidator = [];

        // Add error message to params if it exists, and translate it
        if (isset($validator['message'])) {
            $validator = array_merge(['self' => $fieldName], $validator);
            $params['message'] = $this->translator->translate($validator['message'], $validator);
        }

        // Match validator name to transform method
        try {
            $transformed = match ($validatorName) {
                'required'      => $this->transformGeneric('notEmpty', $params),
                'length'        => $this->transformLength($validator, $params),
                'integer'       => $this->transformGeneric('integer', $params),
                'numeric'       => $this->transformGeneric('numeric', $params),
                'range'         => $this->transformRange($validator, $params),
                'array'         => $this->transformArray($validator, $params),
                'email'         => $this->transformGeneric('emailAddress', $params),
                'matches'       => $this->transformMatch($validator, $params),
                'not_matches'   => $this->transformNotMatch($validator, $params),
                'member_of'     => $this->transformMemberOf($validator, $params),
                'not_member_of' => $this->transformNotMemberOf($validator, $params),
            };
        } catch (UnhandledMatchError $e) {
            return $transformedValidator;
        }

        // Assign result of transformation to $transformedValidator
        list($label, $params) = $transformed;
        $transformedValidator[$label] = $params;

        return $transformedValidator;
    }

    /**
     * Transform a generic validator that doesn't require to mutate the params.
     *
     * @param string  $label
     * @param mixed[] $params
     *
     * @return array{string, mixed[]}
     */
    private function transformGeneric(string $label, array $params): array
    {
        return [$label, $params];
    }

    /**
     * @param mixed[] $validator
     * @param mixed[] $params
     *
     * @return array{string, mixed[]}
     */
    private function transformLength(array $validator, array $params): array
    {
        // Mutate params from validator definition
        if (isset($validator['min'])) {
            $params['min'] = $validator['min'];
        }
        if (isset($validator['max'])) {
            $params['max'] = $validator['max'];
        }

        return ['stringLength', $params];
    }

    /**
     * @param mixed[] $validator
     * @param mixed[] $params
     *
     * @return array{string, mixed[]}
     */
    private function transformRange(array $validator, array $params): array
    {
        // Mutate params from validator definition
        if (isset($validator['min'])) {
            $params['min'] = $validator['min'];
        }
        if (isset($validator['max'])) {
            $params['max'] = $validator['max'];
        }

        // Define label
        if (isset($validator['min']) && isset($validator['max'])) {
            $label = 'between';
        } elseif (isset($validator['min'])) {
            $label = 'greaterThan';
        } else {
            $label = 'lessThan';
        }

        return [$label, $params];
    }

    /**
     * @param mixed[] $validator
     * @param mixed[] $params
     *
     * @return array{string, mixed[]}
     */
    private function transformArray(array $validator, array $params): array
    {
        if (isset($validator['min'])) {
            $params['min'] = $validator['min'];
        }
        if (isset($validator['max'])) {
            $params['max'] = $validator['max'];
        }

        return ['choice', $params];
    }

    /**
     * @param mixed[] $validator
     * @param mixed[] $params
     *
     * @return array{string, mixed[]}
     */
    private function transformMatch(array $validator, array $params): array
    {
        if (isset($validator['field'])) {
            $params['field'] = $validator['field'];
        }

        return ['identical', $params];
    }

    /**
     * @param mixed[] $validator
     * @param mixed[] $params
     *
     * @return array{string, mixed[]}
     */
    private function transformNotMatch(array $validator, array $params): array
    {
        if (isset($validator['field'])) {
            $params['field'] = $validator['field'];
        }

        return ['different', $params];
    }

    /**
     * @param mixed[] $validator
     * @param mixed[] $params
     *
     * @return array{string, mixed[]}
     */
    private function transformMemberOf(array $validator, array $params): array
    {
        if (isset($validator['values'])) {
            $params['regexp'] = '^' . implode('|', $validator['values']) . '$';
        }

        return ['regexp', $params];
    }

    /**
     * @param mixed[] $validator
     * @param mixed[] $params
     *
     * @return array{string, mixed[]}
     */
    private function transformNotMemberOf(array $validator, array $params): array
    {
        if (isset($validator['values'])) {
            $params['regexp'] = '^(?!' . implode('|', $validator['values']) . '$).*$';
        }

        return ['regexp', $params];
    }
}
