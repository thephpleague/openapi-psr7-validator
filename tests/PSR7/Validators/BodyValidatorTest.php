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
     * @return array<array<string,string>> of arguments
     */
    public function dataProviderGreen() : array
    {
        return [
            // Normal message
                [
                    __DIR__ . '/../../stubs/form-url-encoded.yaml',
                    <<<HTTP
POST /urlencoded/scalar-types HTTP/1.1
Content-Length: 428
Content-Type: application/x-www-form-urlencoded; charset=utf-8

address=Moscow%2C+ulitsa+Rusakova%2C+d.15&id=59731930-a95a-11e9-a2a3-2a2ae2dbcce4&phones%5B0%5D=123-456&phones%5B1%5D=456-789&phones%5B%5D=101-112
HTTP
                ],
                [
                    __DIR__ . '/../../stubs/multi-media-types.yaml',
                    <<<HTTP
GET /get-multi-media-type HTTP/1.1
Accept: image/png


HTTP
                ,
                ],
            [
                __DIR__ . '/../../stubs/multi-media-types.yaml',
                <<<HTTP
GET /get-multi-media-type HTTP/1.1
Accept: image/*


HTTP
                ,
            ],
            [
                __DIR__ . '/../../stubs/multi-media-types.yaml',
                <<<HTTP
GET /get-multi-media-type HTTP/1.1
Accept: */*


HTTP
                ,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGreen
     */
    public function testValidateGreen(string $specFile, string $message) : void
    {
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
