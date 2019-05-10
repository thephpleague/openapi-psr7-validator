<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use Exception;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Throwable;

class Nullable extends BaseKeyword
{
    /**
     * Allows sending a null value for the defined schema. Default value is false.
     *
     * @param mixed $data
     */
    public function validate($data, bool $nullable) : void
    {
        try {
            if (! $nullable && ($data === null)) {
                throw new Exception('Value cannot be null');
            }
        } catch (Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword('nullable', $data, $e->getMessage(), $e);
        }
    }
}
