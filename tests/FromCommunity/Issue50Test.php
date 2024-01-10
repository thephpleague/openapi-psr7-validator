<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use PHPUnit\Framework\TestCase;

use function json_encode;

final class Issue50Test extends TestCase
{
    /**
     * @see https://github.com/lezhnev74/openapi-psr7-validator/issues/50
     */
    public function testIssue50(): void
    {
        $yaml = /** @lang yaml */
            <<<YAML
openapi: 3.0.0
info:
  title: Product import API
  version: '1.0'
servers:
  - url: 'http://localhost:8000/api/v1'
paths:
  /products.create:
    post:
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [body]
              properties:
                body:
                  type: object
                  required: [username, email]
                  properties:
                    username:
                      type: string
                    email:
                      type: string 
YAML;

        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();

        $psrRequest = (new ServerRequest('post', 'http://localhost:8000/api/v1/products.create'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(
                Utils::streamFor(
                    json_encode(
                        [
                            'body' =>
                                [
                                    'username' => 'scaytrase',
                                    'notEmail' => 'notEmail',
                                ],
                        ]
                    )
                )
            );

        try {
            $validator->validate($psrRequest);
            self::fail('Should throw an exception');
        } catch (InvalidBody $exception) {
            /** @var KeywordMismatch $previous */
            $previous = $exception->getPrevious();
            self::assertInstanceOf(KeywordMismatch::class, $previous);
            self::assertEquals(['body', 'email'], $previous->dataBreadCrumb()->buildChain());
        }
    }
}
