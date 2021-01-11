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

class MinProperties extends BaseKeyword
{
    /**
     * The value of this keyword MUST be an integer.  This integer MUST be
     * greater than, or equal to, 0.
     *
     * An object instance is valid against "minProperties" if its number of
     * properties is greater than, or equal to, the value of this keyword.
     *
     * If this keyword is not present, it may be considered present with a
     * value of 0.
     *
     * @param mixed $data
     *
     * @throws KeywordMismatch
     */
    public function validate($data, int $minProperties): void
    {
        try {
            Validator::arrayType()->assert($data);
            Validator::trueVal()->assert($minProperties >= 0);
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        if (count($data) < $minProperties) {
            throw KeywordMismatch::fromKeyword(
                'minProperties',
                $data,
                sprintf("The number of object's properties must be greater or equal to %d", $minProperties)
            );
        }
    }
}
