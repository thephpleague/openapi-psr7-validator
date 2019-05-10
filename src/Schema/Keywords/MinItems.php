<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use Exception;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;
use Throwable;
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
     */
    public function validate($data, int $minItems) : void
    {
        try {
            Validator::arrayType()->assert($data);
            Validator::intVal()->assert($minItems);
            Validator::trueVal()->assert($minItems >= 0);

            if (count($data) < $minItems) {
                throw new Exception(sprintf('Size of an array must be greater or equal to %d', $minItems));
            }
        } catch (Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword('minItems', $data, $e->getMessage());
        }
    }
}
