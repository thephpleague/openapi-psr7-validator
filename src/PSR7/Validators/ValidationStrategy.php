<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Validators;


use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

trait ValidationStrategy
{
    /**
     * Distinguish requests and responses, so we can treat them differently (writeOnly/readOnly OAS keywords)
     *
     * @param MessageInterface $message
     * @return int
     */
    protected function detectValidationStrategy(MessageInterface $message): int
    {
        if ($message instanceof ResponseInterface) {
            return \OpenAPIValidation\Schema\Validator::VALIDATE_AS_RESPONSE;
        } else {
            return \OpenAPIValidation\Schema\Validator::VALIDATE_AS_REQUEST;
        }
    }
}