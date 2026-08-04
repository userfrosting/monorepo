<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Fortress\Validator;

use UnhandledMatchError;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\I18n\Translator;
use Valitron\Validator;

/**
 * Loads validation rules from a schema and validates a target array of data.
 */
class ServerSideValidator implements ServerSideValidatorInterface
{
    protected Validator $validator;

    /**
     * Create a new server-side validator. Create the underlying Valitron validator.
     *
     * @param Translator $translator A Translator to be used to translate message ids found in the schema.
     */
    public function __construct(protected Translator $translator)
    {
        // TODO: use locale of translator to determine Valitron language?
        $this->validator = new Validator();
        $this->addCustomRules();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(RequestSchemaInterface $schema, array $data = []): array
    {
        $this->validator->reset();                            // Reset the validator
        $this->validator = $this->validator->withData($data); // Set the data to validate
        $this->generateSchemaRules($schema);                  // Register rules from schema
        $this->validator->validate();                         // Validate

        // Return errors
        // @phpstan-ignore-next-line - Since no param is used, return value will always be array
        return $this->validator->errors();
    }

    /**
     * Add a rule to the validator, along with a specified error message if that rule is failed by the data.
     *
     * @param string      $rule       The name of the validation rule.
     * @param string|null $messageSet The message to display when validation against this rule fails.
     */
    protected function ruleWithMessage(string $rule, ?string $messageSet = null): void
    {
        // Weird way to adapt with Valitron's funky interface
        $params = array_merge([$rule], array_slice(func_get_args(), 2));
        call_user_func_array([$this->validator, 'rule'], $params);

        if ($messageSet !== null) {
            $this->validator->message($messageSet);
        }
    }

    /**
     * Generate and add rules from the schema.
     *
     * @param RequestSchemaInterface $schema
     */
    protected function generateSchemaRules(RequestSchemaInterface $schema): void
    {
        foreach ($schema->all() as $fieldName => $field) {
            if (!isset($field['validators'])) {
                continue;
            }

            $validators = $field['validators'];
            foreach ($validators as $validatorName => $validator) {
                // Skip messages that are for client-side use only
                if (isset($validator['domain']) && $validator['domain'] === 'client') {
                    continue;
                }

                // Generate translated message
                if (isset($validator['message'])) {
                    $params = array_merge(['self' => $fieldName], $validator);
                    $messageSet = $this->translator->translate($validator['message'], $params);
                } else {
                    $messageSet = null;
                }

                try {
                    match ($validatorName) {
                        'array'                  => $this->ruleWithMessage('array', $messageSet, $fieldName), // For now, just check that it is an array.  Really we need a new validation rule here.
                        'email'                  => $this->ruleWithMessage('email', $messageSet, $fieldName),
                        'equals'                 => $this->ruleWithMessage('equalsValue', $messageSet, $fieldName, $validator['value'], $validator['caseSensitive']),
                        'integer'                => $this->ruleWithMessage('integer', $messageSet, $fieldName),
                        'matches'                => $this->ruleWithMessage('equals', $messageSet, $fieldName, $validator['field']),
                        'member_of'              => $this->ruleWithMessage('in', $messageSet, $fieldName, $validator['values'], true),    // Strict comparison
                        'no_leading_whitespace'  => $this->ruleWithMessage('regex', $messageSet, $fieldName, "/^\S.*$/"),
                        'no_trailing_whitespace' => $this->ruleWithMessage('regex', $messageSet, $fieldName, "/^.*\S$/"),
                        'not_equals'             => $this->ruleWithMessage('notEqualsValue', $messageSet, $fieldName, $validator['value'], $validator['caseSensitive']),
                        'not_matches'            => $this->ruleWithMessage('different', $messageSet, $fieldName, $validator['field']),
                        'not_member_of'          => $this->ruleWithMessage('notIn', $messageSet, $fieldName, $validator['values'], true),  // Strict comparison
                        'numeric'                => $this->ruleWithMessage('numeric', $messageSet, $fieldName),
                        'regex'                  => $this->ruleWithMessage('regex', $messageSet, $fieldName, '/' . $validator['regex'] . '/'),
                        'required'               => $this->ruleWithMessage('required', $messageSet, $fieldName),
                        'telephone'              => $this->ruleWithMessage('phoneUS', $messageSet, $fieldName),
                        'uri'                    => $this->ruleWithMessage('url', $messageSet, $fieldName),
                        'username'               => $this->ruleWithMessage('username', $messageSet, $fieldName),
                        'range'                  => call_user_func(function () use ($validator, $messageSet, $fieldName) {
                            if (isset($validator['min'])) {
                                $this->ruleWithMessage('min', $messageSet, $fieldName, $validator['min']);
                            }
                            if (isset($validator['max'])) {
                                $this->ruleWithMessage('max', $messageSet, $fieldName, $validator['max']);
                            }
                        }),
                        'length' => call_user_func(function () use ($validator, $messageSet, $fieldName) {
                            if (isset($validator['min']) && isset($validator['max'])) {
                                $this->ruleWithMessage('lengthBetween', $messageSet, $fieldName, $validator['min'], $validator['max']);
                            } else {
                                if (isset($validator['min'])) {
                                    $this->ruleWithMessage('lengthMin', $messageSet, $fieldName, $validator['min']);
                                }
                                if (isset($validator['max'])) {
                                    $this->ruleWithMessage('lengthMax', $messageSet, $fieldName, $validator['max']);
                                }
                            }
                        }),
                    };
                } catch (UnhandledMatchError $e) {
                    continue;
                }
            }
        }
    }

    /**
     * Add custom rules to the validator.
     */
    protected function addCustomRules(): void
    {
        $this->validator->addInstanceRule('equalsValue', [CustomValidatorRules::class, 'validateEqualsValue']);
        $this->validator->addInstanceRule('notEqualsValue', [CustomValidatorRules::class, 'validateNotEqualsValue']);
        $this->validator->addInstanceRule('phoneUS', [CustomValidatorRules::class, 'validatePhoneUS']);
        $this->validator->addInstanceRule('username', [CustomValidatorRules::class, 'validateUsername']);
    }
}
