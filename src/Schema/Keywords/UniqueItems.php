<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class UniqueItems extends BaseKeyword
{
    /**
     * The value of this keyword MUST be a boolean.
     *
     * If this keyword has boolean value false, the instance validates
     * successfully.  If it has boolean value true, the instance validates
     * successfully if all of its elements are unique.
     *
     * If not present, this keyword may be considered present with boolean
     * value false.
     *
     * @param $data
     * @param bool $uniqueItems
     */
    public function validate($data, bool $uniqueItems): void
    {
        try {
            Validator::arrayType()->assert($data);

            if (array_unique($data) != count($data)) {
                throw new \Exception(sprintf("All array items must be unique"));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("uniqueItems", $data, $e->getMessage());
        }
    }
}