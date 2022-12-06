<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7\Validators;

use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class BodyValidatorTest extends TestCase
{
    /**
     * @return string[][] of arguments
     */
    public function dataProviderGreen(): array
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
,
            ],
            [
                __DIR__ . '/../../stubs/form-url-encoded.yaml',
                <<<HTTP
POST /urlencoded/scalar-deserialization HTTP/1.1
Content-Length: 428
Content-Type: application/x-www-form-urlencoded; charset=utf-8

id=123.0&secure=TRUE&code=-114
HTTP
,
            ],
            [
                __DIR__ . '/../../stubs/multi-media-types.yaml',
                <<<HTTP
POST /post-media-range HTTP/1.1
Content-Type: text/plain
Content-Length: 3

abc
HTTP
,
            ],
            [
                __DIR__ . '/../../stubs/multi-media-types.yaml',
                <<<HTTP
POST /post-media-range HTTP/1.1
Content-Type: text/html
Content-Length: 13

<html></html>
HTTP
,
            ],
            [
                __DIR__ . '/../../stubs/multi-media-types.yaml',
                <<<HTTP
POST /post-media-range HTTP/1.1
Content-Type: application/json
Content-Length: 1

1
HTTP
,
            ],
        ];
    }

    /**
     * @return string[][] of arguments
     */
    public function dataProviderRed(): array
    {
        return [
            // invalid int
            [
                __DIR__ . '/../../stubs/form-url-encoded.yaml',
                <<<HTTP
POST /urlencoded/scalar-deserialization HTTP/1.1
Content-Length: 428
Content-Type: application/x-www-form-urlencoded; charset=utf-8

id=123.0&secure=TRUE&code=-114.123
HTTP
,
            ],
            // invalid bool
            [
                __DIR__ . '/../../stubs/form-url-encoded.yaml',
                <<<HTTP
POST /urlencoded/scalar-deserialization HTTP/1.1
Content-Length: 428
Content-Type: application/x-www-form-urlencoded; charset=utf-8

id=123.0&secure=TRUE1&code=-114
HTTP
,
            ],
            // invalid int
            [
                __DIR__ . '/../../stubs/form-url-encoded.yaml',
                <<<HTTP
POST /urlencoded/scalar-deserialization HTTP/1.1
Content-Length: 428
Content-Type: application/x-www-form-urlencoded; charset=utf-8

id=0x01&secure=TRUE&code=-114
HTTP
,
            ],
            // missing parameter
            [
                __DIR__ . '/../../stubs/form-url-encoded.yaml',
                <<<HTTP
POST /urlencoded/scalar-deserialization HTTP/1.1
Content-Length: 428
Content-Type: application/x-www-form-urlencoded; charset=utf-8

id=1&code=-114
HTTP
,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGreen
     */
    public function testValidateGreen(string $specFile, string $message): void
    {
        $request       = Message::parseRequest($message); // convert a text HTTP message to a PSR7 message
        $serverRequest = new ServerRequest(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $request->getBody()
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $opAddress = $validator->validate($serverRequest);
        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider dataProviderRed
     */
    public function testValidateRed(string $specFile, string $message): void
    {
        $request       = Message::parseRequest($message); // convert a text HTTP message to a PSR7 message
        $serverRequest = new ServerRequest(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $request->getBody()
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $this->expectException(InvalidBody::class);
        $opAddress = $validator->validate($serverRequest);
    }
}
