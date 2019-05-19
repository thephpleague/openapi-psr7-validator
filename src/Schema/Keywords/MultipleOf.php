<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\InvalidSchema;
use OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;
use function sprintf;

class MultipleOf extends BaseKeyword
{
    /**
     * The value of "multipleOf" MUST be a number, strictly greater than 0.
     * A numeric instance is only valid if division by this keyword's value results in an integer.
     *
     * @param mixed     $data
     * @param int|float $multipleOf
     *
     * @throws KeywordMismatch
     */
    public function validate($data, $multipleOf) : void
    {
        try {
            Validator::numeric()->assert($data);
            Validator::numeric()->positive()->assert($multipleOf);

            $value = $data / $multipleOf;
            if ((float) ($value - (int) $value) !== 0.0) {
                throw KeywordMismatch::fromKeyword('multipleOf', $data, sprintf('Division by %d did not resulted in integer', $multipleOf));
            }
        } catch (ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }
    }
}
