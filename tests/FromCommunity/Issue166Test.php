<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidQueryArgs;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Tests\PSR7\BaseValidatorTest;

use function parse_str;

/**
 * @see https://github.com/thephpleague/openapi-psr7-validator/issues/166
 */
final class Issue166Test extends BaseValidatorTest
{
    public function testIssue166(): void
    {
        $yaml = /** @lang yaml */
            <<<YAML
openapi: 3.0.0
servers:
  - url: 'http://localhost:8000/api'
paths:
  /list:
    get:
        parameters:
            - in: query
              name: filter
              style: deepObject
              allowReserved: true
              schema:
                type: object
                properties:
                  filters:
                    oneOf:
                        -
                            type: object
                            required:
                              - max
                            properties:
                              max:
                                type: number
                        -
                            type: object
                            required:
                              - min
                            properties:
                              min:
                                type: number
YAML;

        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();

        $queryString = 'filter[filters][min]=1';
        $query       = null;
        parse_str($queryString, $query);

        $psrRequest = (new ServerRequest('get', 'http://localhost:8000/api/list'))
            ->withHeader('Content-Type', 'application/json')
            ->withQueryParams($query);

        try {
            $validator->validate($psrRequest);
            $this->assertTrue(true);
        } catch (InvalidQueryArgs $e) {
            self::fail('Should not throw an exception');
        }
    }
}
