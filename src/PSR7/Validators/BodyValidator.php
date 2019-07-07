<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use OpenAPIValidation\PSR7\MessageValidator;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\SpecFinder;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use const JSON_ERROR_NONE;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;
use function strtok;

final class BodyValidator implements MessageValidator
{
    private const HEADER_CONTENT_TYPE = 'Content-Type';
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
        $mediaTypeSpecs = $this->finder->findBodySpec($addr);

        if (empty($mediaTypeSpecs)) {
            // edge case: if "content" keyword is not set (body can be anything as no expectations set)
            return;
        }

        $contentTypes = $message->getHeader(self::HEADER_CONTENT_TYPE);
        if (! $contentTypes) {
            throw InvalidHeaders::becauseOfMissingRequiredHeader(self::HEADER_CONTENT_TYPE, $addr);
        }
        $contentType = $contentTypes[0]; // use the first value

        // As per https://tools.ietf.org/html/rfc7231#section-3.1.1.5 and https://tools.ietf.org/html/rfc7231#section-3.1.1.1
        // ContentType can contain multiple statements (type/subtype + parameters), ie: 'multipart/form-data; charset=utf-8; boundary=__X_PAW_BOUNDARY__'
        // OpenAPI Spec only defines the first part of the header value (type/subtype)
        // Other parameters SHOULD be skipped
        $contentType = strtok($contentType, ';');

        // does the response contain one of described media types?
        if (! isset($mediaTypeSpecs[$contentType])) {
            throw InvalidBody::becauseContentTypeIsNotExpected($contentType, $addr);
        }

        // ok looks good, now apply validation
        $body = (string) $message->getBody();

        if (preg_match('#^application/.*json$#', $contentType)) {
            $body = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw InvalidBody::becauseBodyIsNotValidJson(json_last_error_msg(), $addr);
            }
        }

        $validator = new SchemaValidator($this->detectValidationStrategy($message));
        try {
            $validator->validate($body, $mediaTypeSpecs[$contentType]->schema);
        } catch (SchemaMismatch $e) {
            throw InvalidBody::becauseBodyDoesNotMatchSchema($contentType, $addr, $e);
        }
    }
}
