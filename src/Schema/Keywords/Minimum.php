<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Respect\Validation\Exceptions\Exception;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Rules\NumericVal;
use Respect\Validation\Validator;

use function class_exists;
use function sprintf;

class Minimum extends BaseKeyword
{
    /**
     * The value of "minimum" MUST be a number, representing a lower limit
     * for a numeric instance.
     *
     * If the instance is a number, then this keyword validates if
     * "exclusiveMinimum" is true and instance is greater than the provided
     * value, or else if the instance is greater than or exactly equal to
     * the provided value.
     *
     * The value of "exclusiveMinimum" MUST be a boolean, representing
     * whether the limit in "minimum" is exclusive or not.  An undefined
     * value is the same as false.
     *
     * If "exclusiveMinimum" is true, then a numeric instance SHOULD NOT be
     * equal to the value specified in "minimum".  If "exclusiveMinimum" is
     * false (or not specified), then a numeric instance MAY be equal to the
     * value of "minimum".
     *
     * @param mixed     $data
     * @param int|float $minimum
     *
     * @throws KeywordMismatch
     */
    public function validate($data, $minimum, bool $exclusiveMinimum = false): void
    {
        try {
            if (class_exists(NumericVal::class)) {
                Validator::numericVal()->assert($data);
                Validator::numericVal()->assert($minimum);
            } else {
                Validator::numeric()->assert($data);
                Validator::numeric()->assert($minimum);
            }
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        if ($exclusiveMinimum && $data <= $minimum) {
            throw KeywordMismatch::fromKeyword(
                'minimum',
                $data,
                sprintf('Value %d must be greater than %d', $data, $minimum)
            );
        }

        if (! $exclusiveMinimum && $data < $minimum) {
            throw KeywordMismatch::fromKeyword(
                'minimum',
                $data,
                sprintf('Value %d must be greater or equal to %d', $data, $minimum)
            );
        }
    }
}
