<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class MultipleOf extends BaseKeyword
{
    /**
     * The value of "multipleOf" MUST be a number, strictly greater than 0.
     * A numeric instance is only valid if division by this keyword's value results in an integer.
     *
     * @param mixed $data
     * @param number $multipleOf
     */
    public function validate($data, $multipleOf): void
    {
        try {
            Validator::numeric()->assert($data);
            Validator::numeric()->positive()->assert($multipleOf);

            $d = $data % $multipleOf;
            if ($d) {
                throw new \Exception(sprintf("Division by %d did not resulted in integer", $multipleOf));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("multipleOf", $data, $e->getMessage(), $e);
        }
    }
}