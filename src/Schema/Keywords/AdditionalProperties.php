<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

class AdditionalProperties extends BaseKeyword
{
    /**
     * @param mixed $data
     */
    public function validate($data, bool $additionalProperties): void
    {
        // The additionalProperties keyword specifies the type of values in the dictionary.
        // Values can be primitives (strings, numbers or boolean values), arrays or objects.

        // Not used. See \OpenAPIValidation\Schema\Keywords\Properties::validate for usage
    }
}
