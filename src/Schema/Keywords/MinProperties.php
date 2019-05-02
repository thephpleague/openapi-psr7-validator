<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class MinProperties extends BaseKeyword
{
    /**
     * The value of this keyword MUST be an integer.  This integer MUST be
     * greater than, or equal to, 0.
     *
     * An object instance is valid against "minProperties" if its number of
     * properties is greater than, or equal to, the value of this keyword.
     *
     * If this keyword is not present, it may be considered present with a
     * value of 0.
     *
     *
     * @param $data
     * @param int $minProperties
     */
    public function validate($data, $minProperties): void
    {
        try {
            Validator::arrayType()->assert($data);
            Validator::trueVal()->assert($minProperties >= 0);

            if (count($data) < $minProperties) {
                throw new \Exception(sprintf("The number of object's properties must be greater or equal to %d", $minProperties));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("minProperties", $data, $e->getMessage(), $e);
        }
    }
}