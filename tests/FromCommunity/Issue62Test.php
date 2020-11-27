<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\UploadedFile;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class Issue62Test extends TestCase
{
    /**
     * @see https://github.com/thephpleague/openapi-psr7-validator/issues/62
     */
    public function testIssue62(): void
    {
        $yaml = /** @lang yaml */
            <<<YAML
openapi: 3.0.0
info:
  title: Test API
  version: '1.0'
servers:
  - url: 'http://localhost:8000/api/v1'
paths:
  /clam/scan:
    put:
      responses:
        '202':
          description: Accepted
      requestBody:
        content:
          multipart/form-data:
            schema:
              type: object
              properties:
                upload:
                  type: array
                  items:
                    type: string
                    format: binary
YAML;

        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();

        $psrRequest = (new ServerRequest('post', 'http://localhost:8000/api/v1/clam/scan'))
            ->withUploadedFiles(
                ServerRequest::normalizeFiles(
                    [
                        'upload' => [
                            new UploadedFile('body1', 5, 0, 'upload_1.txt', 'text/plain'),
                            new UploadedFile('body2', 5, 0, 'upload_2.txt', 'text/plain'),
                        ],
                    ]
                )
            )
            ->withMethod('PUT')
            ->withHeader('Content-Type', 'multipart/form-data');

        $validator->validate($psrRequest);
        $this->addToAssertionCount(1);
    }
}
