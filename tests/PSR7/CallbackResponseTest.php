<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\Response;
use League\OpenAPIValidation\PSR7\CallbackAddress;
use League\OpenAPIValidation\PSR7\CallbackResponseValidator;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Psr\Http\Message\ResponseInterface;

use function json_encode;

final class CallbackResponseTest extends BaseValidatorTest
{
    public function testItValidatesMessageGreen(): void
    {
        $response = $this->createResponse(json_encode(['success' => true]));

        $validator = $this->createValidator();
        $validator->validate($this->getCallbackAddress(), $response);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesBodyHasInvalidPayloadRed(): void
    {
        $response  = $this->createResponse('[]');
        $validator = $this->createValidator();

        $this->expectException(InvalidBody::class);
        $this->expectExceptionMessage(
            'Body does not match schema for content-type "application/json" for Callback [post /callback somethingHappened post 200]'
        );

        $validator->validate($this->getCallbackAddress(), $response);
    }

    private function createResponse(string $body): ResponseInterface
    {
        $headers = ['Content-Type' => 'application/json'];

        return new Response(200, $headers, $body);
    }

    private function getCallbackAddress(): CallbackAddress
    {
        return new CallbackAddress('/callback', 'post', 'somethingHappened', 'post');
    }

    private function createValidator(): CallbackResponseValidator
    {
        return (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getCallbackResponseValidator();
    }
}
