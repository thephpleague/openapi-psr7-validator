<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class MinLength extends BaseKeyword
{
    /**
     * A string instance is valid against this keyword if its length is
     * greater than, or equal to, the value of this keyword.
     *
     * The length of a string instance is defined as the number of its
     * characters as defined by RFC 7159 [RFC7159].
     *
     * The value of this keyword MUST be an integer.  This integer MUST be
     * greater than, or equal to, 0.
     *
     * "minLength", if absent, may be considered as being present with
     * integer value 0.
     *
     * @param $data
     * @param int $minLength
     */
    public function validate($data, $minLength): void
    {
        try {
            Validator::stringType()->assert($data);
            Validator::intVal()->assert($minLength);
            Validator::trueVal()->assert($minLength >= 0);

            if (mb_strlen($data) < $minLength) {
                throw new \Exception(sprintf("Length of '%d' must be longer or equal to %d", $data, $minLength));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("minLength", $data, $e->getMessage(), $e);
        }
    }
}