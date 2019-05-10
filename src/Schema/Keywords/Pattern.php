<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use Exception;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;
use Throwable;
use function preg_match;
use function sprintf;
use function strlen;

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
     * @param mixed $data
     */
    public function validate($data, string $pattern) : void
    {
        try {
            Validator::stringType()->assert($data);
            Validator::stringType()->assert($pattern);

            // add anchors
            if ($pattern[0] !== $pattern[strlen($pattern) - 1]) {
                $pattern = sprintf('#%s#', $pattern);
            }

            if (! preg_match($pattern, $data)) {
                throw new Exception(sprintf('Data does not match pattern \'%s\'', $pattern));
            }
        } catch (Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword('pattern', $data, $e->getMessage());
        }
    }
}
