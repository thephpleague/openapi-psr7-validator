<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7\Validators;

use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\parse_request;

class BodyValidatorTest extends TestCase
{
    /**
     * @return array<array<string>> of arguments
     */
    public function dataProviderFormUrlencodedGreen() : array
    {
        return [
            // Normal message
            [
                <<<HTTP
POST /urlencoded/scalar-types HTTP/1.1
Content-Length: 428
Content-Type: application/x-www-form-urlencoded; charset=utf-8

address=Moscow%2C+ulitsa+Rusakova%2C+d.15&id=59731930-a95a-11e9-a2a3-2a2ae2dbcce4&phones%5B0%5D=123-456&phones%5B1%5D=456-789&phones%5B%5D=101-112
HTTP
,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderFormUrlencodedGreen
     */
    public function testValidateFormUrlencodedGreen(string $message) : void
    {
        $specFile = __DIR__ . '/../../stubs/form-url-encoded.yaml';

        $request       = parse_request($message); // convert a text HTTP message to a PSR7 message
        $serverRequest = new ServerRequest(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $request->getBody()
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($serverRequest);
        $this->addToAssertionCount(1);
    }
}
