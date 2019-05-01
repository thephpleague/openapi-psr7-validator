<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class MaxItems extends BaseKeyword
{
    /**
     * The value of this keyword MUST be an integer.  This integer MUST be
     * greater than, or equal to, 0.
     *
     * An array instance is valid against "maxItems" if its size is less
     * than, or equal to, the value of this keyword.
     *
     * @param $data
     * @param int $maxItems
     */
    public function validate($data, $maxItems): void
    {
        try {
            Validator::arrayType()->assert($data);
            Validator::intVal()->assert($maxItems);
            Validator::trueVal()->assert($maxItems >= 0);

            if (count($data) > $maxItems) {
                throw new \Exception(sprintf("Size of an array must be less or equal to %d", $maxItems));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("maxItems", $data, $e->getMessage());
        }
    }
}