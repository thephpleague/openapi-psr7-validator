<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\InvalidSchema;
use OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;
use function array_unique;
use function count;

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
     * @param mixed $data
     *
     * @throws KeywordMismatch
     */
    public function validate($data, bool $uniqueItems) : void
    {
        if (! $uniqueItems) {
            return;
        }

        try {
            Validator::arrayType()->assert($data);

            if (array_unique($data) !== count($data)) {
                throw KeywordMismatch::fromKeyword('uniqueItems', $data, 'All array items must be unique');
            }
        } catch (ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }
    }
}
