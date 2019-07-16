<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Header;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Type as CebeType;
use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use OpenAPIValidation\PSR7\MessageValidator;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\SpecFinder;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\Exception\TypeMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Riverline\MultiPartParser\Converters\PSR7;
use Riverline\MultiPartParser\StreamedPart;
use RuntimeException;
use const JSON_ERROR_NONE;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;
use function sprintf;
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

    /**
     * @throws InvalidBody
     * @throws InvalidHeaders
     * @throws NoPath
     */
    public function validate(OperationAddress $addr, MessageInterface $message) : void
    {
        $mediaTypeSpecs = $this->finder->findBodySpec($addr);

        if (empty($mediaTypeSpecs)) {
            // edge case: if "content" keyword is not set (body can be anything as no expectations set)
            return;
        }

        $contentType = $this->readContentType($addr, $message);

        // does the response contain one of described media types?
        if (! isset($mediaTypeSpecs[$contentType])) {
            throw InvalidHeaders::becauseContentTypeIsNotExpected($contentType, $addr);
        }
        $schema = $mediaTypeSpecs[$contentType]->schema;
        if (! $schema) {
            return;
        }

        // Prepare data for validation
        if (preg_match('#^multipart/.*#', $contentType)) {
            $this->validateMultipart($addr, $message, $mediaTypeSpecs, $contentType);
        } else {
            $this->validateUnipart($addr, $message, $schema, $contentType);
        }
    }

    private function readContentType(OperationAddress $addr, MessageInterface $message) : string
    {
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

        return $contentType;
    }

    /**
     * @see https://swagger.io/docs/specification/describing-request-body/multipart-requests/
     *
     * @param MediaType[] $mediaTypeSpecs
     */
    private function validateMultipart(OperationAddress $addr, MessageInterface $message, array $mediaTypeSpecs, string $contentType) : void
    {
        $schema = $mediaTypeSpecs[$contentType]->schema;

        // 0. Multipart body message MUST be described with a set of object properties
        if ($schema->type !== CebeType::OBJECT) {
            throw TypeMismatch::becauseTypeDoesNotMatch('object', $schema->type);
        }

        // 1. Parse message body
        $document = PSR7::convert($message);

        // 2. Validate bodies of each part
        $body = $this->prepareMultipartData($addr, $document);

        $validator = new SchemaValidator($this->detectValidationStrategy($message));
        try {
            $validator->validate($body, $schema);
        } catch (SchemaMismatch $e) {
            throw InvalidBody::becauseBodyDoesNotMatchSchema($contentType, $addr, $e);
        }

        // 3. Validate specified part encodings and headers
        $encodings = $mediaTypeSpecs[$contentType]->encoding;

        foreach ($encodings as $name => $encoding) {
            $parts = $document->getPartsByName($name);
            if (! $parts) {
                throw new RuntimeException(sprintf(
                    'SPecified body part %s is not found',
                    $name
                ));
            }

            foreach ($parts as $part) {
                // 3.1 parts encoding
                $partContentType = $part->getHeader(self::HEADER_CONTENT_TYPE);
                if ($encoding->contentType !== $partContentType) {
                    throw InvalidBody::becauseBodyPartDoesNotMatchSchema(
                        $name,
                        $partContentType,
                        $addr
                    );
                }

                // 3.2. parts headers
                foreach ($encoding->headers as $headerName => $header) {
                    /** @var Header $header */
                    $headerSchema = $header->schema;

                    $validator = new SchemaValidator($this->detectValidationStrategy($message));
                    try {
                        $validator->validate($body, $schema);
                    } catch (SchemaMismatch $e) {
                        throw InvalidBody::becauseBodyDoesNotMatchSchema($contentType, $addr, $e);
                    }
                }
            }
        }
    }

    /**
     * Prepare a Multipart message body (a set of normal parts) for validation against a schema
     *
     * @see https://www.w3.org/Protocols/rfc1341/7_2_Multipart.html
     * @see https://swagger.io/docs/specification/describing-request-body/multipart-requests/
     *
     * @return mixed[]
     *
     * @throws InvalidBody
     * @throws TypeMismatch
     */
    private function prepareMultipartData(OperationAddress $addr, StreamedPart $document) : array
    {
        $multipartData = []; // a buffer to fill up with message parts
        foreach ($document->getParts() as $i => $part) {
            $partContentType = $part->getHeader('Content-Type');

            if (preg_match('#^application/.*json$#', $partContentType)) {
                $partBody = json_decode($part->getBody(), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw InvalidBody::becauseBodyIsNotValidJson(json_last_error_msg(), $addr);
                }
            } else {
                $partBody = $part->getBody();
            }

            // if name is not set, it should be validated with "additionalProperties" keyword
            $multipartData[$part->getName() ?? '____' . $i] = $partBody;
        }

        return $multipartData;
    }

    /**
     * @param MediaType[] $mediaTypeSpecs
     *
     * @throws InvalidBody
     */
    private function validateUnipart(OperationAddress $addr, MessageInterface $message, array $mediaTypeSpecs, string $contentType) : void
    {
        if (preg_match('#^application/.*json$#', $contentType)) {
            $body = json_decode((string) $message->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw InvalidBody::becauseBodyIsNotValidJson(json_last_error_msg(), $addr);
            }
        } else {
            $body = (string) $message->getBody();
        }

        $validator = new SchemaValidator($this->detectValidationStrategy($message));
        $schema    = $mediaTypeSpecs[$contentType]->schema;
        try {
            $validator->validate($body, $schema);
        } catch (SchemaMismatch $e) {
            throw InvalidBody::becauseBodyDoesNotMatchSchema($contentType, $addr, $e);
        }
    }
}
