<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Fortress;

use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\Validator\ServerSideValidator as Validator;
use UserFrosting\I18n\Translator;

/**
 * Loads validation rules from a schema and validates a target array of data.
 *
 * @deprecated 5.1 Use `\UserFrosting\Fortress\Validator\ServerSideValidator` instead
 */
class ServerSideValidator implements ServerSideValidatorInterface
{
    /** @var mixed[] */
    protected array $errors = [];

    /** @var mixed[] */
    protected array $data = [];

    /**
     * Create a new server-side validator.
     *
     * @param RequestSchemaInterface $schema     A RequestSchemaInterface object, containing the validation rules.
     * @param Translator             $translator A Translator to be used to translate message ids found in the schema.
     */
    public function __construct(
        protected RequestSchemaInterface $schema,
        protected Translator $translator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setSchema(RequestSchemaInterface $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslator(Translator $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $data = []): bool
    {
        $validator = new Validator($this->translator);
        $this->data = $data;
        $this->errors = $validator->validate($this->schema, $data);

        return count($this->errors) === 0;
    }

    /**
     *  Get array of fields and data.
     *
     * @return mixed[]
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Get array of error messages.
     *
     * @param null|string $field
     *
     * @return mixed[]|bool
     */
    public function errors(?string $field = null)
    {
        if ($field !== null) {
            return isset($this->errors[$field]) ? $this->errors[$field] : false;
        }

        return $this->errors;
    }
}
