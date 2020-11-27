<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class IssueWithRefTest extends TestCase
{
    public function testIssueWithRef(): void
    {
        $yamlFile  = __DIR__ . '/../stubs/ref-as-individual-path.yaml';
        $validator = (new ValidatorBuilder())->fromYamlFile($yamlFile)->getServerRequestValidator();

        $validator->validate($this->makeRequest());
        $this->addToAssertionCount(1);
    }

    protected function makeRequest(): ServerRequest
    {
        return new ServerRequest(
            'GET',
            'http://localhost:8000/foo'
        );
    }
}
