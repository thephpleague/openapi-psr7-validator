<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;

class Nullable extends BaseKeyword
{
    /**
     * Allows sending a null value for the defined schema. Default value is false.
     *
     * @param mixed $data
     *
     * @throws ValidationKeywordFailed
     */
    public function validate($data, bool $nullable) : void
    {
        if (! $nullable && ($data === null)) {
            throw ValidationKeywordFailed::fromKeyword('nullable', $data, 'Value cannot be null');
        }
    }
}
