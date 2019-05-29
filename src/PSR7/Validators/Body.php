<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\MediaType as MediaTypeSpec;
use Exception;
use OpenAPIValidation\PSR7\Exception\NoContentType;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use RuntimeException;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;

class Body
{
    use ValidationStrategy;

    /**
     * @param MediaTypeSpec[] $mediaTypeSpecs
     *
     * @throws Exception
     */
    public function validate(MessageInterface $message, array $mediaTypeSpecs) : void
    {
        $contentTypes = $message->getHeader('Content-Type');
        if (! $contentTypes) {
            throw new NoContentType();
        }
        $contentType = $contentTypes[0]; // use the first value

        // does the response contain one of described media types?
        if (! isset($mediaTypeSpecs[$contentType])) {
            throw new RuntimeException($contentType, 100);
        }

        // ok looks good, now apply validation
        $body = (string) $message->getBody();

        if (preg_match('#^application/.*json$#', $contentType)) {
            $body = json_decode($body, true);
            if (json_last_error()) {
                throw new RuntimeException('Unable to decode JSON body content: ' . json_last_error_msg());
            }
        }

        $validator = new SchemaValidator($this->detectValidationStrategy($message));
        $validator->validate($body, $mediaTypeSpecs[$contentType]->schema);
    }
}
