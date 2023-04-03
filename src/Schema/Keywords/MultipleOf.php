<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Respect\Validation\Rules\NumericVal;
use Respect\Validation\Validator;
use Throwable;

use function class_exists;
use function round;
use function sprintf;

class MultipleOf extends BaseKeyword
{
    private const EPSILON = 0.00000001;

    /**
     * The value of "multipleOf" MUST be a number, strictly greater than 0.
     * A numeric instance is only valid if division by this keyword's value results in an integer.
     *
     * @param mixed     $data
     * @param int|float $multipleOf
     *
     * @throws KeywordMismatch
     */
    public function validate($data, $multipleOf): void
    {
        try {
            if (class_exists(NumericVal::class)) {
                Validator::numericVal()->assert($data);
                Validator::numericVal()->positive()->assert($multipleOf);
            } else {
                Validator::numeric()->assert($data);
                Validator::numeric()->positive()->assert($multipleOf);
            }
        } catch (Throwable $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        $value = round($data / $multipleOf, 8);
        if ($value - (int) $value > self::EPSILON) {
            throw KeywordMismatch::fromKeyword('multipleOf', $data, sprintf('Division by %s did not resulted in integer', $multipleOf));
        }
    }
}
