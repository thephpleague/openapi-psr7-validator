<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

use function sprintf;

final class HeadersTest extends BaseValidatorTest
{
    public function testItValidatesRequestQueryArgumentsGreen(): void
    {
        $request = (new ServerRequest('get', new Uri('/path1')))
            ->withQueryParams([
                'queryArgA' => 20,
                'queryArgB[]' => [
                    'value1',
                    'value2',
                ],
            ])
            ->withHeader('header-a', 'value A');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItDeserializesRequestHeaderParametersGreen(): void
    {
        $request = (new ServerRequest('get', new Uri('/deserialize-headers')))
            ->withHeader('num', '-1.2')
            ->withHeader('int', '414')
            ->withHeader('bool', 'true');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    /**
     * @return mixed[][]
     */
    public function dataProviderDeserializesRequestHeaderRed(): array
    {
        return [
            ['num', '-'],
            ['num', 'ac'],
            ['int', 'ac'],
            ['int', '1.0'],
            ['bool', '1'],
            ['bool', 'yes'],
            ['bool', ''],
        ];
    }

    /**
     * @dataProvider dataProviderDeserializesRequestHeaderRed
     */
    public function testItDeserializesRequestHeaderParametersRed(string $headerName, string $headerValue): void
    {
        $request = (new ServerRequest('get', new Uri('/deserialize-headers')))
            ->withHeader($headerName, $headerValue);

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();

        $this->expectException(InvalidHeaders::class);
        $this->expectExceptionMessage(
            sprintf('Value "%s" for header "%s" is invalid for Request [get /deserialize-headers]', $headerValue, $headerName)
        );
        $validator->validate($request);
    }
}
