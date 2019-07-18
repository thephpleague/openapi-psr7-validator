<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators\BodyValidator;

use cebe\openapi\spec\MediaType;
use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use OpenAPIValidation\PSR7\MessageValidator;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\Validators\ValidationStrategy;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use const JSON_ERROR_NONE;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;

class UnipartValidator implements MessageValidator
{
    use ValidationStrategy;

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
    public function validate(OperationAddress $addr, MessageInterface $message) : void
    {
        if (preg_match('#^application/.*json$#', $this->contentType)) {
            $body = json_decode((string) $message->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw InvalidBody::becauseBodyIsNotValidJson(json_last_error_msg(), $addr);
            }
        } else {
            $body = (string) $message->getBody();
        }

        $validator = new SchemaValidator($this->detectValidationStrategy($message));
        $schema    = $this->mediaTypeSpec->schema;
        try {
            $validator->validate($body, $schema);
        } catch (SchemaMismatch $e) {
            throw InvalidBody::becauseBodyDoesNotMatchSchema($this->contentType, $addr, $e);
        }
    }
}
