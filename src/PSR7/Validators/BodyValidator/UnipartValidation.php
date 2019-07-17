<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators\BodyValidator;

use cebe\openapi\spec\MediaType;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use const JSON_ERROR_NONE;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;

trait UnipartValidation
{
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
