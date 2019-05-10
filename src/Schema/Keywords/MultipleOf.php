<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use Exception;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;
use Throwable;
use function sprintf;

class MultipleOf extends BaseKeyword
{
    /**
     * The value of "multipleOf" MUST be a number, strictly greater than 0.
     * A numeric instance is only valid if division by this keyword's value results in an integer.
     *
     * @param mixed     $data
     * @param int|float $multipleOf
     */
    public function validate($data, $multipleOf) : void
    {
        try {
            Validator::numeric()->assert($data);
            Validator::numeric()->positive()->assert($multipleOf);

            if ($data % $multipleOf) {
                throw new Exception(sprintf('Division by %d did not resulted in integer', $multipleOf));
            }
        } catch (Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword('multipleOf', $data, $e->getMessage(), $e);
        }
    }
}
