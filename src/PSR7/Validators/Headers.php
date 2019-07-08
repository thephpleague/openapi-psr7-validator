<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Header as HeaderSpec;
use GuzzleHttp\Psr7\Response;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use RuntimeException;

class Headers
{
    use ValidationStrategy;

    /**
     * @param HeaderSpec[] $headerSpecs
     */
    public function validate(MessageInterface $message, array $headerSpecs) : void
    {
        // By default this will not report unexpected headers (soft validation)
        // TODO, maybe this can be enabled later and controlled by custom options
        // in such case throw new \RuntimeException($header, 200);

        if (count($headerSpecs) == 0) {
            return;
        }
        $validator = new SchemaValidator($this->detectValidationStrategy($message));
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
            foreach ($message->getHeader($header) as $headerValue) {
                $validator->validate($headerValue, $spec->schema);
            }
        }
    }
}
