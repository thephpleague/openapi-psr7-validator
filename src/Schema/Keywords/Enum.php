<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class Enum extends BaseKeyword
{
    /**
     * The value of this keyword MUST be an array.  This array SHOULD have
     * at least one element.  Elements in the array SHOULD be unique.
     *
     * Elements in the array MAY be of any type, including null.
     *
     * An instance validates successfully against this keyword if its value
     * is equal to one of the elements in this keyword's array value.
     *
     * @param $data
     * @param array $enum
     */
    public function validate($data, array $enum): void
    {
        try {
            Validator::arrayType()->assert($enum);
            Validator::trueVal()->assert(count($enum) >= 1);

            if (!in_array($data, $enum, true)) {
                throw new \Exception(sprintf("Value must be present in the enum"));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("enum", $data, $e->getMessage(), $e);
        }
    }
}