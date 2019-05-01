<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class Required
{
    /**
     * The value of this keyword MUST be an array.  This array MUST have at
     * least one element.  Elements of this array MUST be strings, and MUST
     * be unique.
     *
     * An object instance is valid against this keyword if its property set
     * contains all elements in this keyword's array value.
     *
     * @param $data
     * @param array $required
     */
    public function validate($data, $required): void
    {
        try {
            Validator::objectType()->assert($data);
            Validator::arrayType()->assert($required);
            Validator::each(Validator::stringType())->assert($required);
            Validator::trueVal()->assert(count(array_unique($required)) === count($required));

            foreach ($required as $reqProperty) {
                $propertyFound = false;
                foreach ($data as $property => $value) {
                    $propertyFound = $propertyFound || ($reqProperty === $property);
                }

                if (!$propertyFound) {
                    throw new \Exception(sprintf("Required property %s must be present in the object", $reqProperty));
                }
            }


        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("required", $data, $e->getMessage());
        }
    }
}