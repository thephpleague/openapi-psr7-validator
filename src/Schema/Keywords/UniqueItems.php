<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Respect\Validation\Exceptions\Exception;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

use function array_map;
use function array_unique;
use function count;
use function var_export;

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
    public function validate($data, bool $uniqueItems): void
    {
        if (! $uniqueItems) {
            return;
        }

        try {
            Validator::arrayType()->assert($data);
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        $items = $data;
        if (count($data)) {
            $items = array_map(static function ($item) {
                return var_export($item, true);
            }, $data);
        }

        if (count(array_unique($items)) !== count($items)) {
            throw KeywordMismatch::fromKeyword('uniqueItems', $data, 'All array items must be unique');
        }
    }
}
