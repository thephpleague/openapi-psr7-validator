<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators;

use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;

final class HeadersValidator implements MessageValidator
{
    use ValidationStrategy;

    /** @var SpecFinder */
    private $finder;

    public function __construct(SpecFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function validate(OperationAddress $addr, MessageInterface $message): void
    {
        $headerSpecs = $this->finder->findHeaderSpecs($addr);

        $validator = new SchemaValidator($this->detectValidationStrategy($message));

        // Check if message misses required headers
        foreach ($headerSpecs as $header => $spec) {
            if ($spec->required && ! $message->hasHeader($header)) {
                throw InvalidHeaders::becauseOfMissingRequiredHeader($header, $addr);
            }

            $parameter = SerializedParameter::fromSpec($spec);

            foreach ($message->getHeader($header) as $headerValue) {
                try {
                    $validator->validate($parameter->deserialize($headerValue), $spec->schema);
                } catch (SchemaMismatch $exception) {
                    throw InvalidHeaders::becauseValueDoesNotMatchSchema($header, $headerValue, $addr, $exception);
                }
            }
        }
    }
}
