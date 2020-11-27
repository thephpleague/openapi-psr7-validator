<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Parameter;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidParameter;
use League\OpenAPIValidation\PSR7\Exception\Validation\RequiredParameterMissing;
use League\OpenAPIValidation\Schema\BreadCrumb;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;

use function array_key_exists;

/**
 * Validates given array against given specs
 */
class ArrayValidator
{
    /** @var Parameter[] */
    private $specs;

    /**
     * @param Parameter[] $specs
     */
    public function __construct(array $specs)
    {
        $this->specs = $specs;
    }

    /**
     * @param mixed[] $params
     *
     * @throws InvalidParameter
     * @throws RequiredParameterMissing
     */
    public function validateArray(array $params, int $validationStrategy): void
    {
        foreach ($this->specs as $name => $spec) {
            if ($spec->required && ! array_key_exists($name, $params)) {
                throw RequiredParameterMissing::fromName($name);
            }
        }

        // Note: By default, OpenAPI treats all request parameters as optional.
        $validator = new SchemaValidator($validationStrategy);

        foreach ($params as $name => $argumentValue) {
            // skip if there is no spec for this argument
            if (! array_key_exists($name, $this->specs)) {
                continue;
            }

            $parameter = SerializedParameter::fromSpec($this->specs[$name]);
            try {
                $validator->validate($parameter->deserialize($argumentValue), $parameter->getSchema(), new BreadCrumb($name));
            } catch (SchemaMismatch $e) {
                throw InvalidParameter::becauseValueDidNotMatchSchema($name, $argumentValue, $e);
            }
        }
    }
}
