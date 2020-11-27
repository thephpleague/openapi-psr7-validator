<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\Request;
use League\OpenAPIValidation\PSR7\CallbackAddress;
use League\OpenAPIValidation\PSR7\CallbackRequestValidator;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Psr\Http\Message\RequestInterface;

use function json_encode;

final class CallbackRequestTest extends BaseValidatorTest
{
    public function testItValidatesMessageGreen(): void
    {
        $request = $this->createRequest(json_encode(['status' => 'created']));

        $validator = $this->createValidator();
        $validator->validate($this->getCallbackAddress(), $request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesBodyHasInvalidPayloadRed(): void
    {
        $request   = $this->createRequest('[]');
        $validator = $this->createValidator();

        $this->expectException(InvalidBody::class);
        $this->expectExceptionMessage(
            'Body does not match schema for content-type "application/json" for Callback [post /callback somethingHappened post]'
        );

        $validator->validate($this->getCallbackAddress(), $request);
    }

    private function createRequest(string $body): RequestInterface
    {
        $headers = ['Content-Type' => 'application/json'];

        return new Request('post', 'https://some-callback-uri/asdf', $headers, $body);
    }

    private function getCallbackAddress(): CallbackAddress
    {
        return new CallbackAddress('/callback', 'post', 'somethingHappened', 'post');
    }

    private function createValidator(): CallbackRequestValidator
    {
        return (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getCallbackRequestValidator();
    }
}
