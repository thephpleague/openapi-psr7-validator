<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Respect\Validation\Exceptions\Exception;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

use function count;
use function sprintf;

class MinItems extends BaseKeyword
{
    /**
     * The value of this keyword MUST be an integer.  This integer MUST be
     * greater than, or equal to, 0.
     *
     * An array instance is valid against "minItems" if its size is greater
     * than, or equal to, the value of this keyword.
     *
     * If this keyword is not present, it may be considered present with a
     * value of 0.
     *
     * @param mixed $data
     *
     * @throws KeywordMismatch
     */
    public function validate($data, int $minItems): void
    {
        try {
            Validator::arrayType()->assert($data);
            Validator::intVal()->assert($minItems);
            Validator::trueVal()->assert($minItems >= 0);
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        if (count($data) < $minItems) {
            throw KeywordMismatch::fromKeyword('minItems', $data, sprintf('Size of an array must be greater or equal to %d', $minItems));
        }
    }
}
