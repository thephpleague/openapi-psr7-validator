<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators\BodyValidator;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type as CebeType;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\Validators\ValidationStrategy;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\Exception\TypeMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;

use function parse_str;

/**
 * Should validate "application/x-www-form-urlencoded" body types
 */
class FormUrlencodedValidator implements MessageValidator
{
    use ValidationStrategy;
    use BodyDeserialization;

    /** @var MediaType */
    protected $mediaTypeSpec;
    /** @var string */
    protected $contentType;

    public function __construct(MediaType $mediaTypeSpec, string $contentType)
    {
        $this->mediaTypeSpec = $mediaTypeSpec;
        $this->contentType   = $contentType;
    }

    /**
     * @throws NoPath
     * @throws ValidationFailed
     */
    public function validate(OperationAddress $addr, MessageInterface $message): void
    {
        /** @var Schema $schema */
        $schema = $this->mediaTypeSpec->schema;

        // 0. Multipart body message MUST be described with a set of object properties
        if ($schema->type !== CebeType::OBJECT) {
            throw TypeMismatch::becauseTypeDoesNotMatch('object', $schema->type);
        }

        // 1. Parse message body

        $validator = new SchemaValidator($this->detectValidationStrategy($message));
        try {
            $body = $this->deserializeBody($this->parseUrlencodedData($message), $schema);
            $validator->validate($body, $schema);
        } catch (SchemaMismatch $e) {
            throw InvalidBody::becauseBodyDoesNotMatchSchema($this->contentType, $addr, $e);
        }

        // 3. Validate specified part encodings and headers
        // @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#encoding-object
        // The encoding object SHALL only apply to requestBody objects when the media type is multipart or application/x-www-form-urlencoded.
        // An encoding attribute is introduced to give you control over the serialization of parts of multipart request bodies.
        // This attribute is only applicable to "multipart" and "application/x-www-form-urlencoded" request bodies.
        $encodings = $this->mediaTypeSpec->encoding;

        // todo URL Serialization:
        // @see https://github.com/lezhnev74/openapi-psr7-validator/issues/47
    }

    /**
     * @return mixed[]
     */
    protected function parseUrlencodedData(MessageInterface $message): array
    {
        $body = [];

        parse_str(
            (string) $message->getBody(),
            $body
        );

        return $body;
    }
}
