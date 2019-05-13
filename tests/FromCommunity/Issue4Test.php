<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 09 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use PHPUnit\Framework\TestCase;

# @see https://github.com/lezhnev74/openapi-psr7-validator/issues/4
class Issue4Test extends TestCase
{
    public function test_it_resolves_schema_refs_from_yaml_string_green(): void
    {
        $yamlFile = __DIR__ . "/../stubs/SchemaWithRefs.yaml";
        $validator = ServerRequestValidator::fromYamlFile($yamlFile);

        $validator->validate($this->makeRequest());
        $this->addToAssertionCount(1);
    }

    public function test_it_resolves_schema_refs_from_yaml_file_green(): void
    {
        $yamlFile = __DIR__ . "/../stubs/SchemaWithRefs.yaml";
        $validator = ServerRequestValidator::fromYaml(file_get_contents($yamlFile));

        $validator->validate($this->makeRequest());
        $this->addToAssertionCount(1);
    }

    protected function makeRequest(): ServerRequest
    {
        return new ServerRequest(
            'POST',
            'http://localhost:8000/products.create',
            [
                'Content-Type' => 'application/json',
            ],
            <<<JSON
{
    "test": {
        "input": "some data"
    }
}
JSON
        );
    }
}
