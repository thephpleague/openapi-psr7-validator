<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Respect\Validation\Exceptions\Exception;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

use function preg_match;
use function sprintf;
use function str_replace;

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
     *
     * @throws KeywordMismatch
     */
    public function validate($data, string $pattern): void
    {
        try {
            Validator::stringType()->assert($data);
            Validator::stringType()->assert($pattern);
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        $pattern = sprintf('#%s#u', str_replace('#', '\#', $pattern));

        if (! preg_match($pattern, $data)) {
            throw KeywordMismatch::fromKeyword('pattern', $data, sprintf('Data does not match pattern \'%s\'', $pattern));
        }
    }
}
