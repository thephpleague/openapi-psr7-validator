<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators\BodyValidator;

use cebe\openapi\spec\Header;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Type as CebeType;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use OpenAPIValidation\PSR7\OperationAddress;
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

/**
 * Should validate multipart/* body types
 */
trait MultipartValidation
{
    /**
     * @see https://swagger.io/docs/specification/describing-request-body/multipart-requests/
     * @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#requestBodyObject
     *
     * The encoding object SHALL only apply to requestBody objects when the media type is multipart or application/x-www-form-urlencoded.
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
        // @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#encoding-object
        // An encoding attribute is introduced to give you control over the serialization of parts of multipart request bodies.
        // This attribute is only applicable to "multipart" and "application/x-www-form-urlencoded" request bodies.
        $encodings = $mediaTypeSpecs[$contentType]->encoding;

        foreach ($encodings as $name => $encoding) {
            $parts = $document->getPartsByName($name);
            if (! $parts) {
                throw new RuntimeException(sprintf(
                    'Specified body part %s is not found',
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
                foreach ($encoding->headers as $headerName => $headerSpec) {
                    /** @var Header $headerSpec */
                    $headerSchema = $headerSpec->schema;

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
}
