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

class MaxLength extends BaseKeyword
{
    /**
     * The value of this keyword MUST be a non-negative integer.
     *
     * The value of this keyword MUST be an integer.  This integer MUST be
     * greater than, or equal to, 0.
     *
     * A string instance is valid against this keyword if its length is less
     * than, or equal to, the value of this keyword.
     *
     * The length of a string instance is defined as the number of its
     * characters as defined by RFC 7159 [RFC7159].
     *
     * @param mixed $data
     *
     * @throws KeywordMismatch
     */
    public function validate($data, int $maxLength): void
    {
        try {
            Validator::stringType()->assert($data);
            Validator::intType()->assert($maxLength);
            Validator::trueVal()->assert($maxLength >= 0);
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        if (mb_strlen($data) > $maxLength) {
            throw KeywordMismatch::fromKeyword('maxLength', $data, sprintf("Length of '%s' must be shorter or equal to %d", $data, $maxLength));
        }
    }
}
