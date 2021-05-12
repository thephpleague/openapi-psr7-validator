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

class Maximum extends BaseKeyword
{
    /**
     * The value of "maximum" MUST be a number, representing an upper limit
     * for a numeric instance.
     *
     * If the instance is a number, then this keyword validates if
     * "exclusiveMaximum" is true and instance is less than the provided
     * value, or else if the instance is less than or exactly equal to the
     * provided value.
     *
     * The value of "exclusiveMaximum" MUST be a boolean, representing
     * whether the limit in "maximum" is exclusive or not.  An undefined
     * value is the same as false.
     *
     * If "exclusiveMaximum" is true, then a numeric instance SHOULD NOT be
     * equal to the value specified in "maximum".  If "exclusiveMaximum" is
     * false (or not specified), then a numeric instance MAY be equal to the
     * value of "maximum".
     *
     * @param mixed     $data
     * @param int|float $maximum
     *
     * @throws KeywordMismatch
     */
    public function validate($data, $maximum, bool $exclusiveMaximum = false): void
    {
        try {
            if (class_exists(NumericVal::class)) {
                Validator::numericVal()->assert($data);
                Validator::numericVal()->assert($maximum);
            } else {
                Validator::numeric()->assert($data);
                Validator::numeric()->assert($maximum);
            }
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        if ($exclusiveMaximum && $data >= $maximum) {
            throw KeywordMismatch::fromKeyword(
                'maximum',
                $data,
                sprintf('Value %d must be less than %d', $data, $maximum)
            );
        }

        if (! $exclusiveMaximum && $data > $maximum) {
            throw KeywordMismatch::fromKeyword(
                'maximum',
                $data,
                sprintf('Value %d must be less or equal to %d', $data, $maximum)
            );
        }
    }
}
