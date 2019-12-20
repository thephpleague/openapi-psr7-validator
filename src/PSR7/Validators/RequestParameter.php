<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Parameter as CebeParameter;
use cebe\openapi\spec\Schema as CebeSchema;
use League\OpenAPIValidation\Schema\Exception\ContentTypeMismatch;
use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\Exception\TypeMismatch;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;
use const JSON_ERROR_NONE;
use function is_string;
use function json_decode;
use function json_last_error;
use function key;
use function preg_match;
use function reset;

final class RequestParameter
{
    /** @var CebeSchema */
    private $schema;
    /** @var string|null */
    private $contentType;

    public function __construct(CebeSchema $schema, ?string $contentType = null)
    {
        $this->schema      = $schema;
        $this->contentType = $contentType;
    }

    public static function fromSpec(CebeParameter $parameter) : self
    {
        $content = $parameter->content;
        try {
            if ($parameter->schema !== null) {
                Validator::not(Validator::notEmpty())->assert($content);

                return new self($parameter->schema);
            }

            Validator::length(1, 1)->assert($content);
        } catch (ExceptionInterface $e) {
            // If there is a `schema`, `content` must be empty.
            // If there isn't a `schema`, a `content` with exactly 1 property must exist.
            // @see https://swagger.io/docs/specification/describing-parameters/#schema-vs-content
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        $schema      = reset($content)->schema;
        $contentType = key($content);

        return new self($schema, $contentType);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws SchemaMismatch
     */
    public function deserialize($value)
    {
        if ($this->isJsonContentType()) {
            // Value MUST be a string.
            if (! is_string($value)) {
                throw TypeMismatch::becauseTypeDoesNotMatch('string', $value);
            }

            $decodedValue = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ContentTypeMismatch::fromContentType($this->contentType, $value);
            }

            return $decodedValue;
        }

        return $value;
    }

    private function isJsonContentType() : bool
    {
        return $this->contentType !== null && preg_match('#^application/.*json$#', $this->contentType) !== false;
    }

    public function getSchema() : CebeSchema
    {
        return $this->schema;
    }
}
