<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Exceptions;

use Exception;
use Throwable;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\UserMessageException;

/**
 * Validation related Exception.
 */
final class ValidationException extends Exception implements UserMessageException
{
    /**
     * @var string[]
     */
    protected array $errors = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(string $message = '', int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Add errors returned by Valitron/Validator.
     *
     * @param string[][] $errors
     *
     * @return static
     */
    public function addErrors(array $errors): static
    {
        foreach ($errors as $field) {
            foreach ($field as $error) {
                $this->addError($error);
            }
        }

        return $this;
    }

    /**
     * @param string $error
     *
     * @return static
     */
    public function addError(string $error): static
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle(): string
    {
        return 'VALIDATE.ERROR';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return implode(' ', $this->getErrors());
    }
}
