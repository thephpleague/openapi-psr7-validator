<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class Pattern extends BaseKeyword
{
    /**
     * The value of this keyword MUST be a string.  This string SHOULD be a
     * valid regular expression, according to the ECMA 262 regular
     * expression dialect.
     *
     * A string instance is considered valid if the regular expression
     * matches the instance successfully.  Recall: regular expressions are
     * not implicitly anchored.
     *
     * @param $data
     * @param string $pattern
     */
    public function validate($data, $pattern): void
    {
        try {
            Validator::stringType()->assert($data);
            Validator::stringType()->assert($pattern);

            // add anchors
            if ($pattern[0] != $pattern[strlen($pattern) - 1]) {
                $pattern = "#$pattern#";
            }

            if (!preg_match($pattern, $data)) {
                throw new \Exception(sprintf("Data does not match pattern '%s'", $pattern));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("pattern", $data, $e->getMessage());
        }
    }
}