<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Respect\Validation\Exceptions\Exception;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

use function mb_strlen;
use function sprintf;

class MinLength extends BaseKeyword
{
    /**
     * A string instance is valid against this keyword if its length is
     * greater than, or equal to, the value of this keyword.
     *
     * The length of a string instance is defined as the number of its
     * characters as defined by RFC 7159 [RFC7159].
     *
     * The value of this keyword MUST be an integer.  This integer MUST be
     * greater than, or equal to, 0.
     *
     * "minLength", if absent, may be considered as being present with
     * integer value 0.
     *
     * @param mixed $data
     *
     * @throws KeywordMismatch
     * @throws InvalidSchema
     */
    public function validate($data, int $minLength): void
    {
        try {
            Validator::stringType()->assert($data);
            Validator::intVal()->assert($minLength);
            Validator::trueVal()->assert($minLength >= 0);
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        if (mb_strlen($data) < $minLength) {
            throw KeywordMismatch::fromKeyword(
                'minLength',
                $data,
                sprintf("Length of '%s' must be longer or equal to %d", $data, $minLength)
            );
        }
    }
}
