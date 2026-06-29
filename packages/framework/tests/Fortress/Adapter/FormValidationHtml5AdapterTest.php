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
use UserFrosting\Fortress\Adapter\FormValidationHtml5Adapter;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\I18n\Translator;
use UserFrosting\Tests\Fortress\DictionaryStub;

class FormValidationHtml5AdapterTest extends TestCase
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['email' => 'data-fv-emailaddress=true data-fv-emailaddress-message="Not a valid email address...we think." '];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * N.B.: equals is not a supported validator in Html5FormValidationAdapter.
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['voles' => ''];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['species' => 'data-fv-notempty=true data-fv-notempty-message="Please tell us your species." '];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['species' => 'data-fv-notempty=true '];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['screech' => 'data-fv-stringlength=true data-fv-stringlength-message="Your screech must be between {{min}} and {{max}} characters long." data-fv-stringlength-min=5 data-fv-stringlength-max=10 '];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['screech' => 'data-fv-stringlength=true data-fv-stringlength-message="Your screech must be at least {{min}} characters long." data-fv-stringlength-min=5 '];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['screech' => 'data-fv-stringlength=true data-fv-stringlength-message="Your screech must be no more than {{max}} characters long." data-fv-stringlength-max=10 '];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['voles' => 'data-fv-integer=true data-fv-integer-message="Voles must be numeric." '];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['voles' => 'data-fv-between=true data-fv-between-message="You must catch {{min}} - {{max}} voles." data-fv-between-min=5 data-fv-between-max=10 '];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['voles' => 'data-fv-greaterthan=true data-fv-greaterthan-message="You must catch at least {{min}} voles." data-fv-greaterthan-value=5 '];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['voles' => 'data-fv-lessthan=true data-fv-lessthan-message="You must catch no more than {{max}} voles." data-fv-lessthan-value=10 '];
        $this->assertEquals($expectedResult, $result);
    }

    public function testValidateRangeNone(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'voles' => [
                'validators' => [
                    'range' => [
                        'value'   => 10,
                        'message' => 'You must catch no more than {{value}} voles.',
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['voles' => ''];
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

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = ['voles' => 'data-fv-choice=true data-fv-choice-message="You must choose between {{min}} and {{max}} voles." data-fv-choice-min=5 data-fv-choice-max=10 '];
        $this->assertEquals($expectedResult, $result);
    }

    public function testValidateMatches(): void
    {
        // Arrange
        $schema = new RequestSchema([
            'password'  => [],
            'passwordc' => [
                'validators' => [
                    'matches' => [
                        'field'   => 'password',
                        'message' => "The value of this field does not match the value of the '{{field}}' field.",
                    ],
                ],
            ],
        ]);

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $result = $adapter->rules($schema);

        // Assert
        $expectedResult = [
            'password'  => '',
            'passwordc' => 'data-fv-identical=true data-fv-identical-message="The value of this field does not match the value of the \'{{field}}\' field." data-fv-identical-field=password ',
        ];
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

        // Set exception assertion
        $this->expectException(\Exception::class); // TODO: Custom Exception

        // Act
        $adapter = new FormValidationHtml5Adapter();
        $adapter->rules($schema);
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
        $adapter = new FormValidationHtml5Adapter();

        // Test with html5 format
        $result = $adapter->rules($schema);
        $expectedResult = ['plumage' => ''];
        $this->assertEquals($expectedResult, $result);
    }
}
