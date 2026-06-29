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
use UserFrosting\Fortress\Adapter\FormValidationArrayAdapter;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\I18n\Translator;
use UserFrosting\Tests\Fortress\DictionaryStub;

class FormValidationArrayAdapterTest extends TestCase
{
    protected Translator $translator;

    public function setUp(): void
    {
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
            'email' => [
                'validators' => [
                    'emailAddress' => [
                        'message' => 'Not a valid email address...we think.',
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * N.B.: equals is not a supported validator in ArrayValidationAdapter.
     * Let's test what's happening when this happens.
     */
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

        $expectedResult = [
            'voles' => [
                'validators' => [
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'species' => [
                'validators' => [
                    'notEmpty' => [
                        'message' => 'Please tell us your species.',
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'species' => [
                'validators' => [
                    'notEmpty' => [],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'screech' => [
                'validators' => [
                    'stringLength' => [
                        'message' => 'Your screech must be between 5 and 10 characters long.',
                        'min'     => 5,
                        'max'     => 10,
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'screech' => [
                'validators' => [
                    'stringLength' => [
                        'message' => 'Your screech must be at least 5 characters long.',
                        'min'     => 5,
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'screech' => [
                'validators' => [
                    'stringLength' => [
                        'message' => 'Your screech must be no more than 10 characters long.',
                        'max'     => 10,
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'voles' => [
                'validators' => [
                    'integer' => [
                        'message' => 'Voles must be numeric.',
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'accuracy' => [
                'validators' => [
                    'numeric' => [
                        'message' => 'Sorry, your strike accuracy must be a number.',
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'voles' => [
                'validators' => [
                    'between' => [
                        'message' => 'You must catch 5 - 10 voles.',
                        'min'     => 5,
                        'max'     => 10,
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function testValidateRangeMin(): void
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

        $expectedResult = [
            'voles' => [
                'validators' => [
                    'greaterThan' => [
                        'message' => 'You must catch at least 5 voles.',
                        'min'     => 5,
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function testValidateRangeMax(): void
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

        $expectedResult = [
            'voles' => [
                'validators' => [
                    'lessThan' => [
                        'message' => 'You must catch no more than 10 voles.',
                        'max'     => 10,
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function testValidateArray(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'voles' => [
                'validators' => [
                    'array' => [
                        'min'     => 5,
                        'max'     => 10,
                        'message' => 'You must choose between {{min}} and {{max}} voles.',
                    ],
                ],
            ],
        ]);

        $expectedResult = [
            'voles' => [
                'validators' => [
                    'choice' => [
                        'message' => 'You must choose between 5 and 10 voles.',
                        'min'     => 5,
                        'max'     => 10,
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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
            'passwordc' => [
                'validators' => [],
            ],
        ]);

        $expectedResult = [
            'password' => [
                'validators' => [
                    'identical' => [
                        'message' => "The value of this field does not match the value of the 'passwordc' field.",
                        'field'   => 'passwordc',
                    ],
                ],
            ],
            'passwordc' => [
                'validators' => [],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function testValidateMatchesNoFields(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'password' => [
                'validators' => [
                    'matches' => [
                        'message' => "The value of this field does not match the value of the '{{field}}' field.",
                    ],
                ],
            ],
        ]);

        $expectedResult = [
            'password' => [
                'validators' => [
                    'identical' => [
                        'message' => "The value of this field does not match the value of the '' field.",
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'password' => [
                'validators' => [
                    'different' => [
                        'message' => 'Your password cannot be the same as your username.',
                        'field'   => 'user_name',
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'genus' => [
                'validators' => [
                    'regexp' => [
                        'message' => 'Sorry, that is not one of the permitted genuses.',
                        'regexp'  => '^Megascops|Bubo|Glaucidium|Tyto|Athene$',
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
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

        $expectedResult = [
            'genus' => [
                'validators' => [
                    'regexp' => [
                        'message' => 'Sorry, it would appear that you are not an owl.',
                        'regexp'  => '^(?!Myodes|Microtus|Neodon|Alticola$).*$',
                    ],
                ],
            ],
        ];

        // Act
        $adapter = new FormValidationArrayAdapter($this->translator);
        $result = $adapter->rules($schema);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }
}
