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
use function strtok;

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

        // As per https://tools.ietf.org/html/rfc7231#section-3.1.1.5 and https://tools.ietf.org/html/rfc7231#section-3.1.1.1
        // ContentType can contain multiple statements (type/subtype + parameters), ie: 'multipart/form-data; charset=utf-8; boundary=__X_PAW_BOUNDARY__'
        // OpenAPI Spec only defines the first part of the header value (type/subtype)
        // Other parameters SHOULD be skipped
        $contentType = strtok($contentType, ';');

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
