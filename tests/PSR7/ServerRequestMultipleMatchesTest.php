<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\MultipleOperationsMismatchForRequest;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class ServerRequestMultipleMatchesTest extends TestCase
{
    public function testItMatchesSingleOperationRed(): void
    {
        // This matches at least two paths
        $specFile = __DIR__ . '/../stubs/multipleMatches.yaml';
        $request  = new ServerRequest('get', '/users/goodstring');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItChecksAgainstMultipleMatchedOperationsRed(): void
    {
        // This matches at least two paths
        $specFile = __DIR__ . '/../stubs/multipleMatches.yaml';
        $request  = new ServerRequest('get', '/users/12.33');

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
            $validator->validate($request);
            $this->fail('Exception expected');
        } catch (MultipleOperationsMismatchForRequest $e) {
            $this->assertCount(2, $e->matchedAddrs());
            $this->assertEquals('/users/{id}', $e->matchedAddrs()[0]->path());
            $this->assertEquals('/users/{group}', $e->matchedAddrs()[1]->path());
        }
    }
}
