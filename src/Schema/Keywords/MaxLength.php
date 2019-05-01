<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class MaxLength
{
    /**
     * The value of this keyword MUST be a non-negative integer.
     *
     * The value of this keyword MUST be an integer.  This integer MUST be
     * greater than, or equal to, 0.
     *
     * A string instance is valid against this keyword if its length is less
     * than, or equal to, the value of this keyword.
     *
     * The length of a string instance is defined as the number of its
     * characters as defined by RFC 7159 [RFC7159].
     *
     * @param $data
     * @param int $maxLength
     */
    public function validate($data, $maxLength): void
    {
        try {
            Validator::stringType()->assert($data);
            Validator::intType()->assert($maxLength);
            Validator::trueVal()->assert($maxLength >= 0);

            if (mb_strlen($data) > $maxLength) {
                throw new \Exception(sprintf("Length of '%d' must be shorter or equal to %d", $data, $maxLength));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("maxLength", $data, $e->getMessage());
        }
    }
}