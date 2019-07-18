<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators\BodyValidator;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type as CebeType;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\Exception\TypeMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use function explode;

/**
 * Should validate "application/x-www-form-urlencoded" body types
 */
trait FormUrlencodedValidation
{
    /**
     * @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#requestBodyObject
     *
     * @param MediaType[] $mediaTypeSpecs
     */
    private function validateFormUrlencoded(OperationAddress $addr, MessageInterface $message, array $mediaTypeSpecs, string $contentType) : void
    {
        /** @var Schema $schema */
        $schema = $mediaTypeSpecs[$contentType]->schema;

        // 0. Multipart body message MUST be described with a set of object properties
        if ($schema->type !== CebeType::OBJECT) {
            throw TypeMismatch::becauseTypeDoesNotMatch('object', $schema->type);
        }

        // 1. Parse message body
        $body = $this->parseUrlencodedData($message);

        $validator = new SchemaValidator($this->detectValidationStrategy($message));
        try {
            $validator->validate($body, $schema);
        } catch (SchemaMismatch $e) {
            throw InvalidBody::becauseBodyDoesNotMatchSchema($contentType, $addr, $e);
        }

        // 3. Validate specified part encodings and headers
        // @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#encoding-object
        // The encoding object SHALL only apply to requestBody objects when the media type is multipart or application/x-www-form-urlencoded.
        // An encoding attribute is introduced to give you control over the serialization of parts of multipart request bodies.
        // This attribute is only applicable to "multipart" and "application/x-www-form-urlencoded" request bodies.
        $encodings = $mediaTypeSpecs[$contentType]->encoding;

        // todo URL Serialization:
        // @see https://github.com/lezhnev74/openapi-psr7-validator/issues/47
    }

    /**
     * @return mixed[]
     */
    protected function parseUrlencodedData(MessageInterface $message) : array
    {
        $body = [];

        foreach (explode('&', $message->getBody()->getContents()) as $chunk) {
            [$name, $value] = explode('=', $chunk);
            $body[$name]    = $value;
        }

        return $body;
    }
}
