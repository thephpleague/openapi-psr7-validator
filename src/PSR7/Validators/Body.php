<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Validators;


use cebe\openapi\spec\MediaType as MediaTypeSpec;
use OpenAPIValidation\PSR7\Exception\NoContentType;
use OpenAPIValidation\Schema\Validator as SchemaValidator;
use Psr\Http\Message\MessageInterface;

class Body
{
    use ValidationStrategy;

    /**
     * @param MessageInterface $message
     * @param MediaTypeSpec[] $mediaTypeSpecs
     * @throws \Exception
     */
    public function validate(MessageInterface $message, array $mediaTypeSpecs): void
    {
        $contentTypes = $message->getHeader('Content-Type');
        if (!$contentTypes) {
            throw new NoContentType();
        }
        $contentType = $contentTypes[0]; # use the first value

        // does the response contain one of described media types?
        if (!isset($mediaTypeSpecs[$contentType])) {
            throw new \RuntimeException($contentType, 100);
        }

        // ok looks good, now apply validation
        $body = (string)$message->getBody();
        if (preg_match("#^application/json#", $contentType)) {
            $body = json_decode($body, true);
            if (json_last_error()) {
                throw new \RuntimeException("Unable to decode JSON body content: " . json_last_error_msg());
            }
        }
        $validator = new SchemaValidator($mediaTypeSpecs[$contentType]->schema, $body, $this->detectValidationStrategy($message));
        $validator->validate();
    }
}