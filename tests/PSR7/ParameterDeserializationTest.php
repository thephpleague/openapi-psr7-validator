<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

use function http_build_query;

final class ParameterDeserializationTest extends TestCase
{
    public function testGoodJsonQueryParameter(): void
    {
        $queryParams = ['filter' => '{"type":"t-shirt","color":"blue"}'];
        $psrRequest  = (new ServerRequest(
            'GET',
            'http://localhost:8000/api/v1/products?' . http_build_query($queryParams)
        ))
            ->withQueryParams($queryParams);

        $validator = (new ValidatorBuilder())
            ->fromYamlFile(__DIR__ . '/../stubs/serialized-params.yaml')
            ->getServerRequestValidator();

        $validator->validate($psrRequest);

        $this->addToAssertionCount(1);
    }

    public function testBadJsonQueryParameter(): void
    {
        $queryParams = ['filter' => '{"type":"t-shirt","color":false}'];
        $psrRequest  = (new ServerRequest(
            'GET',
            'http://localhost:8000/api/v1/products?' . http_build_query($queryParams)
        ))
            ->withQueryParams($queryParams);

        $validator = (new ValidatorBuilder())
            ->fromYamlFile(__DIR__ . '/../stubs/serialized-params.yaml')
            ->getServerRequestValidator();

        $this->expectException(ValidationFailed::class);
        $this->expectExceptionMessage('Value "{"type":"t-shirt","color":false}" for argument "filter" is invalid for Request [get /products]');

        $validator->validate($psrRequest);
    }

    public function testInvalidJsonQueryParameter(): void
    {
        $queryParams = ['filter' => 'type,t-shirt,color,blue'];
        $psrRequest  = (new ServerRequest(
            'GET',
            'http://localhost:8000/api/v1/products?' . http_build_query($queryParams)
        ))
            ->withQueryParams($queryParams);

        $validator = (new ValidatorBuilder())
            ->fromYamlFile(__DIR__ . '/../stubs/serialized-params.yaml')
            ->getServerRequestValidator();

        $this->expectException(ValidationFailed::class);
        $this->expectExceptionMessage('Value "type,t-shirt,color,blue" for argument "filter" is invalid for Request [get /products]');

        $validator->validate($psrRequest);
    }
}
