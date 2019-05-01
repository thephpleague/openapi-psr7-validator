<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class MinItems
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
     * @param $data
     * @param int $minItems
     */
    public function validate($data, $minItems): void
    {
        try {
            Validator::arrayType()->assert($data);
            Validator::intVal()->assert($minItems);
            Validator::trueVal()->assert($minItems >= 0);

            if (count($data) < $minItems) {
                throw new \Exception(sprintf("Size of an array must be greater or equal to %d", $minItems));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("minItems", $data, $e->getMessage());
        }
    }
}