<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators\BodyValidator;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Reference;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use League\OpenAPIValidation\PSR7\Validators\ValidationStrategy;
use Psr\Http\Message\MessageInterface;

use function explode;
use function preg_match;
use function strtok;

/**
 * Supports validation for different media types of bodies,
 * including JSON and multipart types
 */
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
    public function validate(OperationAddress $addr, MessageInterface $message): void
    {
        $mediaTypeSpecs = $this->finder->findBodySpec($addr);

        if (empty($mediaTypeSpecs)) {
            // edge case: if "content" keyword is not set (body can be anything as no expectations set)
            return;
        }

        // Detect ContentType of the message
        $contentType = $this->messageContentType($message);
        if (! $contentType) {
            throw InvalidHeaders::becauseOfMissingRequiredHeader(self::HEADER_CONTENT_TYPE, $addr);
        }

        // does the response contain one of described media types?
        $mediaTypeSpec = $this->matchMediaTypeSpec($mediaTypeSpecs, $contentType);
        if ($mediaTypeSpec === null) {
            throw InvalidHeaders::becauseContentTypeIsNotExpected($contentType, $addr);
        }

        // detect the schema for the media type
        $schema = $mediaTypeSpec->schema;
        if (! $schema) {
            // no schema means no validation
            // note: schema is REQUIRED to define the input parameters to the operation when using multipart content
            return;
        }

        // Validate message body
        if (preg_match('#^multipart/.*#', $contentType)) {
            (new MultipartValidator($mediaTypeSpec, $contentType))->validate($addr, $message);
        } elseif (preg_match('#^application/x-www-form-urlencoded$#', $contentType)) {
            (new FormUrlencodedValidator($mediaTypeSpec, $contentType))->validate($addr, $message);
        } else {
            (new UnipartValidator($mediaTypeSpec, $contentType))->validate($addr, $message);
        }
    }

    private function messageContentType(MessageInterface $message): ?string
    {
        $contentTypes = $message->getHeader(self::HEADER_CONTENT_TYPE);
        if (! $contentTypes) {
            return null;
        }

        $contentType = $contentTypes[0]; // use the first value

        // As per https://tools.ietf.org/html/rfc7231#section-3.1.1.5 and https://tools.ietf.org/html/rfc7231#section-3.1.1.1
        // ContentType can contain multiple statements (type/subtype + parameters), ie: 'multipart/form-data; charset=utf-8; boundary=__X_PAW_BOUNDARY__'
        // OpenAPI Spec only defines the first part of the header value (type/subtype)
        // Other parameters SHOULD be skipped
        $contentType = (string) strtok($contentType, ';');

        return $contentType;
    }

    /**
     * Match the spec from media type specs for the given media type.
     *
     * @param Reference[]|MediaType[] $mediaTypeSpecs
     *
     * @return Reference|MediaType|null
     */
    private function matchMediaTypeSpec(array $mediaTypeSpecs, string $mediaType)
    {
        [$mediaTypeType, $mediaTypeSubType] = explode('/', $mediaType);

        // Allow sub-type ranges and match all, like 'image/*', '*/*'
        // In the order: type/subtype > type/* > */*
        // see: https://tools.ietf.org/html/rfc7231#section-5.3.2
        $candidateContentTypes = [
            $mediaType,
            $mediaTypeType . '/*',
            '*/*',
        ];

        foreach ($candidateContentTypes as $type) {
            if (isset($mediaTypeSpecs[$type])) {
                return $mediaTypeSpecs[$type];
            }
        }

        return null;
    }
}
