<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use GuzzleHttp\Psr7\Response;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use OpenAPIValidation\PSR7\MessageValidator;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\SpecFinder;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
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
    public function validate(OperationAddress $addr, MessageInterface $message) : void
    {
        $headerSpecs = $this->finder->findHeaderSpecs($addr);

        $validator = new SchemaValidator($this->detectValidationStrategy($message));

        // Check if message misses required headers
        foreach ($headerSpecs as $header => $spec) {
            if (($message instanceof Response || $spec->required) && ! $message->hasHeader($header)) {
                throw InvalidHeaders::becauseOfMissingRequiredHeader($header, $addr);
            }

            foreach ($message->getHeader($header) as $headerValue) {
                try {
                    $validator->validate($headerValue, $spec->schema);
                } catch (SchemaMismatch $exception) {
                    throw InvalidHeaders::becauseValueDoesNotMatchSchema($header, $headerValue, $addr, $exception);
                }
            }
        }
    }
}
