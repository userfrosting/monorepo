<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Fortress\Adapter;

use PHPUnit\Framework\TestCase;
use UserFrosting\Fortress\Adapter\JqueryValidationArrayAdapter;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\I18n\Translator;
use UserFrosting\Tests\Fortress\DictionaryStub;

class JqueryValidationArrayAdapterTest extends TestCase
{
    protected Translator $translator;

    public function setUp(): void
    {
        // Create a message translator
        $this->translator = new Translator(new DictionaryStub());
    }

    public function testValidateEmail(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'email' => [
                'validators' => [
                    'email' => [
                        'message' => 'Not a valid email address...we think.',
                    ],
                ],
            ],
        ]);

        $expectedResult = [
            'rules' => [
                'email' => [
                    'email' => true,
                ],
            ],
            'messages' => [
                'email' => [
                    'email' => 'Not a valid email address...we think.',
                ],
            ],
        ];

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function testValidateEquals(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'voles' => [
                'validators' => [
                    'equals' => [
                        'value'         => 8,
                        'caseSensitive' => false,
                        'message'       => 'Voles must be equal to {{value}}.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'voles' => [
                    'equals' => [
                        'value'         => 8,
                        'caseSensitive' => false,
                        'message'       => 'Voles must be equal to {{value}}.',
                    ],
                ],
            ],
            'messages' => [
                'voles' => [
                    'equals' => 'Voles must be equal to 8.',
                ],
            ],
        ], $result);
    }

    public function testValidateEqualsWithMissingCondition(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'voles' => [
                'validators' => [
                    'equals' => [
                        'foo'     => 8, // N.B.: Value is expected here
                        'message' => 'Voles must be equal to {{foo}}.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'voles' => [],
            ],
            'messages' => [
                'voles' => [],
            ],
        ], $result);
    }

    public function testValidateInteger(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'voles' => [
                'validators' => [
                    'integer' => [
                        'message' => 'Voles must be numeric.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'voles' => [
                    'digits' => true,
                ],
            ],
            'messages' => [
                'voles' => [
                    'digits' => 'Voles must be numeric.',
                ],
            ],
        ], $result);
    }

    public function testValidateLengthBetween(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'screech' => [
                'validators' => [
                    'length' => [
                        'min'     => 5,
                        'max'     => 10,
                        'message' => 'Your screech must be between {{min}} and {{max}} characters long.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'screech' => [
                    'rangelength' => [5, 10],
                ],
            ],
            'messages' => [
                'screech' => [
                    'rangelength' => 'Your screech must be between 5 and 10 characters long.',
                ],
            ],
        ], $result);
    }

    public function testValidateLengthMin(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'screech' => [
                'validators' => [
                    'length' => [
                        'min'     => 5,
                        'message' => 'Your screech must be at least {{min}} characters long.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'screech' => [
                    'minlength' => 5,
                ],
            ],
            'messages' => [
                'screech' => [
                    'minlength' => 'Your screech must be at least 5 characters long.',
                ],
            ],
        ], $result);
    }

    public function testValidateLengthMax(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'screech' => [
                'validators' => [
                    'length' => [
                        'max'     => 10,
                        'message' => 'Your screech must be no more than {{max}} characters long.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'screech' => [
                    'maxlength' => 10,
                ],
            ],
            'messages' => [
                'screech' => [
                    'maxlength' => 'Your screech must be no more than 10 characters long.',
                ],
            ],
        ], $result);
    }

    public function testValidateMatches(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'password' => [
                'validators' => [
                    'matches' => [
                        'field'   => 'passwordc',
                        'message' => "The value of this field does not match the value of the '{{field}}' field.",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'password' => [
                    'matchFormField' => 'passwordc',
                ],
            ],
            'messages' => [
                'password' => [
                    'matchFormField' => "The value of this field does not match the value of the 'passwordc' field.",
                ],
            ],
        ], $result);
    }

    public function testValidateMatchesWithMissingCondition(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'password' => [
                'validators' => [
                    'matches' => [
                        'value'   => 'passwordc', // N.B.: Should be field here
                        'message' => "The value of this field does not match the value of the '{{value}}' field.",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'password' => [],
            ],
            'messages' => [
                'password' => [],
            ],
        ], $result);
    }

    public function testValidateMemberOf(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'genus' => [
                'validators' => [
                    'member_of' => [
                        'values'  => ['Megascops', 'Bubo', 'Glaucidium', 'Tyto', 'Athene'],
                        'message' => 'Sorry, that is not one of the permitted genuses.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'genus' => [
                    'memberOf' => ['Megascops', 'Bubo', 'Glaucidium', 'Tyto', 'Athene'],
                ],
            ],
            'messages' => [
                'genus' => [
                    'memberOf' => 'Sorry, that is not one of the permitted genuses.',
                ],
            ],
        ], $result);
    }

    public function testValidateNoLeadingWhitespace(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'user_name' => [
                'validators' => [
                    'no_leading_whitespace' => [
                        'message' => "'{{self}}' cannot begin with whitespace characters",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'user_name' => [
                    'noLeadingWhitespace' => true,
                ],
            ],
            'messages' => [
                'user_name' => [
                    'noLeadingWhitespace' => "'user_name' cannot begin with whitespace characters",
                ],
            ],
        ], $result);
    }

    public function testValidateNoTrailingWhitespace(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'user_name' => [
                'validators' => [
                    'no_trailing_whitespace' => [
                        'message' => "'{{self}}' cannot end with whitespace characters",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        $this->assertEquals([
            'rules' => [
                'user_name' => [
                    'noTrailingWhitespace' => true,
                ],
            ],
            'messages' => [
                'user_name' => [
                    'noTrailingWhitespace' => "'user_name' cannot end with whitespace characters",
                ],
            ],
        ], $result);
    }

    public function testValidateNotEquals(): void
    {
        // Arrange
        // TODO: Add missing messages for custom rules.  Maybe upgrade the version of Valitron first.
        $schema = new RequestSchema([
            'voles' => [
                'validators' => [
                    'not_equals' => [
                        'value'         => 0,
                        'caseSensitive' => false,
                        'message'       => 'Voles must not be equal to {{value}}.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'voles' => [
                    'notEquals' => [
                        'value'         => 0,
                        'caseSensitive' => false,
                        'message'       => 'Voles must not be equal to {{value}}.',
                    ],
                ],
            ],
            'messages' => [
                'voles' => [
                    'notEquals' => 'Voles must not be equal to 0.',
                ],
            ],
        ], $result);
    }

    public function testValidateNotMatches(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'password' => [
                'validators' => [
                    'not_matches' => [
                        'field'   => 'user_name',
                        'message' => 'Your password cannot be the same as your username.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'password' => [
                    'notMatchFormField' => 'user_name',
                ],
            ],
            'messages' => [
                'password' => [
                    'notMatchFormField' => 'Your password cannot be the same as your username.',
                ],
            ],
        ], $result);
    }

    public function testValidateNotMemberOf(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'genus' => [
                'validators' => [
                    'not_member_of' => [
                        'values'  => ['Myodes', 'Microtus', 'Neodon', 'Alticola'],
                        'message' => 'Sorry, it would appear that you are not an owl.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'genus' => [
                    'notMemberOf' => ['Myodes', 'Microtus', 'Neodon', 'Alticola'],
                ],
            ],
            'messages' => [
                'genus' => [
                    'notMemberOf' => 'Sorry, it would appear that you are not an owl.',
                ],
            ],
        ], $result);
    }

    public function testValidateNumeric(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'accuracy' => [
                'validators' => [
                    'numeric' => [
                        'message' => 'Sorry, your strike accuracy must be a number.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'accuracy' => [
                    'number' => true,
                ],
            ],
            'messages' => [
                'accuracy' => [
                    'number' => 'Sorry, your strike accuracy must be a number.',
                ],
            ],
        ], $result);
    }

    public function testValidateRange(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'voles' => [
                'validators' => [
                    'range' => [
                        'min'     => 5,
                        'max'     => 10,
                        'message' => 'You must catch {{min}} - {{max}} voles.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'voles' => [
                    'range' => [5, 10],
                ],
            ],
            'messages' => [
                'voles' => [
                    'range' => 'You must catch 5 - 10 voles.',
                ],
            ],
        ], $result);
    }

    public function testValidateMin(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'voles' => [
                'validators' => [
                    'range' => [
                        'min'     => 5,
                        'message' => 'You must catch at least {{min}} voles.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'voles' => [
                    'min' => 5,
                ],
            ],
            'messages' => [
                'voles' => [
                    'min' => 'You must catch at least 5 voles.',
                ],
            ],
        ], $result);
    }

    public function testValidateMax(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'voles' => [
                'validators' => [
                    'range' => [
                        'max'     => 10,
                        'message' => 'You must catch no more than {{max}} voles.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'voles' => [
                    'max' => 10,
                ],
            ],
            'messages' => [
                'voles' => [
                    'max' => 'You must catch no more than 10 voles.',
                ],
            ],
        ], $result);
    }

    public function testValidateRegex(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'screech' => [
                'validators' => [
                    'regex' => [
                        'regex'   => '^who(o*)$',
                        'message' => 'You did not provide a valid screech.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'screech' => [
                    'pattern' => '^who(o*)$',
                ],
            ],
            'messages' => [
                'screech' => [
                    'pattern' => 'You did not provide a valid screech.',
                ],
            ],
        ], $result);
    }

    public function testValidateRequired(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'species' => [
                'validators' => [
                    'required' => [
                        'message' => 'Please tell us your species.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'species' => [
                    'required' => true,
                ],
            ],
            'messages' => [
                'species' => [
                    'required' => 'Please tell us your species.',
                ],
            ],
        ], $result);
    }

    public function testValidateRequiredWithNullValidatorDefinition(): void
    {
        // Arrange: This mirrors YAML `required:` where no nested attributes are defined.
        $schema = new RequestSchema([
            'species' => [
                'validators' => [
                    'required' => null,
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'species' => [
                    'required' => true,
                ],
            ],
            'messages' => [],
        ], $result);
    }

    public function testValidateTelephone(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'phone' => [
                'validators' => [
                    'telephone' => [
                        'message' => 'Whoa there, check your phone number again.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'phone' => [
                    'phoneUS' => true,
                ],
            ],
            'messages' => [
                'phone' => [
                    'phoneUS' => 'Whoa there, check your phone number again.',
                ],
            ],
        ], $result);
    }

    public function testValidateUri(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'website' => [
                'validators' => [
                    'uri' => [
                        'message' => "That's not even a valid URL...",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'website' => [
                    'url' => true,
                ],
            ],
            'messages' => [
                'website' => [
                    'url' => "That's not even a valid URL...",
                ],
            ],
        ], $result);
    }

    public function testValidateUsername(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'user_name' => [
                'validators' => [
                    'username' => [
                        'message' => "Sorry buddy, that's not a valid username.",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'user_name' => [
                    'username' => true,
                ],
            ],
            'messages' => [
                'user_name' => [
                    'username' => "Sorry buddy, that's not a valid username.",
                ],
            ],
        ], $result);
    }

    public function testDomainRulesClientOnly(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'plumage' => [
                'validators' => [
                    'required' => [
                        'domain'  => 'client',
                        'message' => "Are you sure you don't want to show us your plumage?",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'plumage' => [
                    'required' => true,
                ],
            ],
            'messages' => [
                'plumage' => [
                    'required' => "Are you sure you don't want to show us your plumage?",
                ],
            ],
        ], $result);

        // Adding Test with Form array prefix 'coolform1'
        $result1 = $adapter->rules($schema, 'coolform1');
        $this->assertEquals([
            'rules' => [
                'coolform1[plumage]' => [
                    'required' => true,
                ],
            ],
            'messages' => [
                'coolform1[plumage]' => [
                    'required' => "Are you sure you don't want to show us your plumage?",
                ],
            ],
        ], $result1);
    }

    public function testDomainRulesServerOnly(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'plumage' => [
                'validators' => [
                    'required' => [
                        'domain'  => 'server',
                        'message' => "Are you sure you don't want to show us your plumage?",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        $this->assertEquals([
            'rules' => [
                'plumage' => [],
            ],
            'messages' => [],
        ], $result);

        // Adding Test with Form array prefix 'coolform1'
        $result1 = $adapter->rules($schema, 'coolform1');
        $this->assertEquals([
            'rules' => [
                'coolform1[plumage]' => [],
            ],
            'messages' => [],
        ], $result1);
    }

    public function testManyRules(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'user_name' => [
                'validators' => [
                    'length' => [
                        'min'     => 1,
                        'max'     => 50,
                        'message' => 'ACCOUNT_USER_CHAR_LIMIT',
                    ],
                    'no_leading_whitespace' => [
                        'message' => "'{{self}}' must not contain leading whitespace.",
                    ],
                    'no_trailing_whitespace' => [
                        'message' => "'{{self}}' must not contain trailing whitespace.",
                    ],
                    'required' => [
                        'message' => 'ACCOUNT_SPECIFY_USERNAME',
                    ],
                    'username' => [
                        'message' => "'{{self}}' must be a valid username.",
                    ],
                ],
            ],
            'display_name' => [
                'validators' => [
                    'length' => [
                        'min'     => 1,
                        'max'     => 50,
                        'message' => 'ACCOUNT_DISPLAY_CHAR_LIMIT',
                    ],
                    'required' => [
                        'message' => 'ACCOUNT_SPECIFY_DISPLAY_NAME',
                    ],
                ],
            ],
            'secret' => [
                'validators' => [
                    'length' => [
                        'min'     => 1,
                        'max'     => 100,
                        'message' => 'Secret must be between {{ min }} and {{ max }} characters long.',
                        'domain'  => 'client',
                    ],
                    'numeric'  => [],
                    'required' => [
                        'message' => 'Secret must be specified.',
                        'domain'  => 'server',
                    ],
                ],
            ],
            'puppies' => [
                'validators' => [
                    'member_of' => [
                        'values' => [
                            0 => '0',
                            1 => '1',
                        ],
                        'message' => "The value for '{{self}}' must be '0' or '1'.",
                    ],
                ],
                'transformations' => [
                    0 => 'purify',
                    1 => 'trim',
                ],
            ],
            'phone' => [
                'validators' => [
                    'telephone' => [
                        'message' => "The value for '{{self}}' must be a valid telephone number.",
                    ],
                ],
            ],
            'email' => [
                'validators' => [
                    'required' => [
                        'message' => 'ACCOUNT_SPECIFY_EMAIL',
                    ],
                    'length' => [
                        'min'     => 1,
                        'max'     => 100,
                        'message' => 'ACCOUNT_EMAIL_CHAR_LIMIT',
                    ],
                    'email' => [
                        'message' => 'ACCOUNT_INVALID_EMAIL',
                    ],
                ],
            ],
            'password' => [
                'validators' => [
                    'required' => [
                        'message' => 'ACCOUNT_SPECIFY_PASSWORD',
                    ],
                    'length' => [
                        'min'     => 8,
                        'max'     => 50,
                        'message' => 'ACCOUNT_PASS_CHAR_LIMIT',
                    ],
                ],
            ],
            'passwordc' => [
                'validators' => [
                    'required' => [
                        'message' => 'ACCOUNT_SPECIFY_PASSWORD',
                    ],
                    'matches' => [
                        'field'   => 'password',
                        'message' => 'ACCOUNT_PASS_MISMATCH',
                    ],
                    'length' => [
                        'min'     => 8,
                        'max'     => 50,
                        'message' => 'ACCOUNT_PASS_CHAR_LIMIT',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'user_name' => [
                    'rangelength' => [
                        0 => 1,
                        1 => 50,
                    ],
                    'noLeadingWhitespace'  => true,
                    'noTrailingWhitespace' => true,
                    'required'             => true,
                    'username'             => true,
                ],
                'display_name' => [
                    'rangelength' => [
                        0 => 1,
                        1 => 50,
                    ],
                    'required' => true,
                ],
                'secret' => [
                    'rangelength' => [
                        0 => 1,
                        1 => 100,
                    ],
                    'number' => true,
                ],
                'puppies' => [
                    'memberOf' => [
                        0 => '0',
                        1 => '1',
                    ],
                ],
                'phone' => [
                    'phoneUS' => true,
                ],
                'email' => [
                    'required'    => true,
                    'rangelength' => [
                        0 => 1,
                        1 => 100,
                    ],
                    'email' => true,
                ],
                'password' => [
                    'required'    => true,
                    'rangelength' => [
                        0 => 8,
                        1 => 50,
                    ],
                ],
                'passwordc' => [
                    'required'       => true,
                    'matchFormField' => 'password',
                    'rangelength'    => [
                        0 => 8,
                        1 => 50,
                    ],
                ],
            ],
            'messages' => [
                'user_name' => [
                    'rangelength'          => 'ACCOUNT_USER_CHAR_LIMIT',
                    'noLeadingWhitespace'  => "'user_name' must not contain leading whitespace.",
                    'noTrailingWhitespace' => "'user_name' must not contain trailing whitespace.",
                    'required'             => 'ACCOUNT_SPECIFY_USERNAME',
                    'username'             => "'user_name' must be a valid username.",
                ],
                'display_name' => [
                    'rangelength' => 'ACCOUNT_DISPLAY_CHAR_LIMIT',
                    'required'    => 'ACCOUNT_SPECIFY_DISPLAY_NAME',
                ],
                'secret' => [
                    'rangelength' => 'Secret must be between 1 and 100 characters long.',
                ],
                'puppies' => [
                    'memberOf' => "The value for 'puppies' must be '0' or '1'.",
                ],
                'phone' => [
                    'phoneUS' => "The value for 'phone' must be a valid telephone number.",
                ],
                'email' => [
                    'required'    => 'ACCOUNT_SPECIFY_EMAIL',
                    'rangelength' => 'ACCOUNT_EMAIL_CHAR_LIMIT',
                    'email'       => 'ACCOUNT_INVALID_EMAIL',
                ],
                'password' => [
                    'required'    => 'ACCOUNT_SPECIFY_PASSWORD',
                    'rangelength' => 'ACCOUNT_PASS_CHAR_LIMIT',
                ],
                'passwordc' => [
                    'required'       => 'ACCOUNT_SPECIFY_PASSWORD',
                    'matchFormField' => 'ACCOUNT_PASS_MISMATCH',
                    'rangelength'    => 'ACCOUNT_PASS_CHAR_LIMIT',
                ],
            ],
        ], $result);

        // Adding Test with Form array prefix 'coolform1'
        $result1 = $adapter->rules($schema, 'coolform1');
        $this->assertEquals([
            'rules' => [
                'coolform1[user_name]' => [
                    'rangelength' => [
                        0 => 1,
                        1 => 50,
                    ],
                    'noLeadingWhitespace'  => true,
                    'noTrailingWhitespace' => true,
                    'required'             => true,
                    'username'             => true,
                ],
                'coolform1[display_name]' => [
                    'rangelength' => [
                        0 => 1,
                        1 => 50,
                    ],
                    'required' => true,
                ],
                'coolform1[secret]' => [
                    'rangelength' => [
                        0 => 1,
                        1 => 100,
                    ],
                    'number' => true,
                ],
                'coolform1[puppies]' => [
                    'memberOf' => [
                        0 => '0',
                        1 => '1',
                    ],
                ],
                'coolform1[phone]' => [
                    'phoneUS' => true,
                ],
                'coolform1[email]' => [
                    'required'    => true,
                    'rangelength' => [
                        0 => 1,
                        1 => 100,
                    ],
                    'email' => true,
                ],
                'coolform1[password]' => [
                    'required'    => true,
                    'rangelength' => [
                        0 => 8,
                        1 => 50,
                    ],
                ],
                'coolform1[passwordc]' => [
                    'required'       => true,
                    'matchFormField' => 'password',
                    'rangelength'    => [
                        0 => 8,
                        1 => 50,
                    ],
                ],
            ],
            'messages' => [
                'coolform1[user_name]' => [
                    'rangelength'          => 'ACCOUNT_USER_CHAR_LIMIT',
                    'noLeadingWhitespace'  => "'user_name' must not contain leading whitespace.",
                    'noTrailingWhitespace' => "'user_name' must not contain trailing whitespace.",
                    'required'             => 'ACCOUNT_SPECIFY_USERNAME',
                    'username'             => "'user_name' must be a valid username.",
                ],
                'coolform1[display_name]' => [
                    'rangelength' => 'ACCOUNT_DISPLAY_CHAR_LIMIT',
                    'required'    => 'ACCOUNT_SPECIFY_DISPLAY_NAME',
                ],
                'coolform1[secret]' => [
                    'rangelength' => 'Secret must be between 1 and 100 characters long.',
                ],
                'coolform1[puppies]' => [
                    'memberOf' => "The value for 'puppies' must be '0' or '1'.",
                ],
                'coolform1[phone]' => [
                    'phoneUS' => "The value for 'phone' must be a valid telephone number.",
                ],
                'coolform1[email]' => [
                    'required'    => 'ACCOUNT_SPECIFY_EMAIL',
                    'rangelength' => 'ACCOUNT_EMAIL_CHAR_LIMIT',
                    'email'       => 'ACCOUNT_INVALID_EMAIL',
                ],
                'coolform1[password]' => [
                    'required'    => 'ACCOUNT_SPECIFY_PASSWORD',
                    'rangelength' => 'ACCOUNT_PASS_CHAR_LIMIT',
                ],
                'coolform1[passwordc]' => [
                    'required'       => 'ACCOUNT_SPECIFY_PASSWORD',
                    'matchFormField' => 'ACCOUNT_PASS_MISMATCH',
                    'rangelength'    => 'ACCOUNT_PASS_CHAR_LIMIT',
                ],
            ],
        ], $result1);
    }

    public function testValidateNoRule(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'user_name' => [
                'validators' => [
                    'foo' => [
                        'message' => "Sorry buddy, that's not a valid username.",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new JqueryValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals([
            'rules' => [
                'user_name' => [],
            ],
            'messages' => [
                'user_name' => [],
            ],
        ], $result);
    }
}
