<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class Maximum
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
     * @param $data
     * @param number $maximum
     * @param bool $exclusiveMaximum
     */
    public function validate($data, $maximum, bool $exclusiveMaximum = false): void
    {
        try {
            Validator::numeric()->assert($data);
            Validator::numeric()->assert($maximum);

            if ($exclusiveMaximum && $data >= $maximum) {
                throw new \Exception(sprintf("Value %d must be less or equal to %d", $data, $maximum));
            }

            if (!$exclusiveMaximum && $data > $maximum) {
                throw new \Exception(sprintf("Value %d must be less than %d", $data, $maximum));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("maximum", $data, $e->getMessage());
        }
    }
}