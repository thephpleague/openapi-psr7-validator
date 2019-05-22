<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Header as HeaderSpec;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use RuntimeException;
use function array_key_exists;

class Headers
{
    use ValidationStrategy;

    /**
     * @param HeaderSpec[] $headerSpecs
     */
    public function validate(MessageInterface $message, array $headerSpecs) : void
    {
        $messageHeaders = $message->getHeaders();

        foreach ($messageHeaders as $header => $headerValues) {
            if (! array_key_exists($header, $headerSpecs)) {
                // By default this will not report unexpected headers (soft validation)
                // TODO, maybe this can be enabled later and controlled by custom options
                // throw new \RuntimeException($header, 200);
                continue;
            }

            $validator = new SchemaValidator($this->detectValidationStrategy($message));
            foreach ($headerValues as $headerValue) {
                $validator->validate($headerValue, $headerSpecs[$header]->schema);
            }
        }

        // Check if message misses headers
        foreach ($headerSpecs as $header => $spec) {
            if (! $message->hasHeader($header)) {
                throw new RuntimeException($header, 201);
            }
        }
    }
}
