<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Header as HeaderSpec;
use GuzzleHttp\Psr7\Response;
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

        // Check if message misses required headers
        foreach ($headerSpecs as $header => $spec) {
            if ($message instanceof Response) {
                // Responses headers are mandatory (it supports no 'required' keyword)
                if (! $message->hasHeader($header)) {
                    throw new RuntimeException($header, 201);
                }
            } else {
                // request parameters can be optional ('required' keyword is supported)
                if (! $message->hasHeader($header) && $spec->required) {
                    throw new RuntimeException($header, 201);
                }
            }
        }
    }
}
