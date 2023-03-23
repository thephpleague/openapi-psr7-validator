<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidCookies;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

use function sprintf;

final class CookieDeserializeTest extends BaseValidatorTest
{
    /**
     * @return mixed[][]
     */
    public function dataProviderCookiesGreen(): array
    {
        return [
            ['num' , '-1.2'],
            ['int' , '414'],
            ['bool', 'true'],
            ['bool', '1'],
            ['bool', '0'],
        ];
    }

    /**
     * @dataProvider dataProviderCookiesGreen
     */
    public function testItDeserializesServerRequestCookieParametersGreen(string $cookieName, string $cookieValue): void
    {
        $request = (new ServerRequest('get', new Uri('/deserialize-cookies')))
            ->withCookieParams([$cookieName => $cookieValue]);

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    /**
     * @return mixed[][]
     */
    public function dataProviderCookiesRed(): array
    {
        return [
            ['num', '-'],
            ['num', 'ac'],
            ['int', 'ac'],
            ['int', '1.0'],
            ['bool', '2'],
            ['bool', 'yes'],
            ['bool', ''],
        ];
    }

    /**
     * @dataProvider dataProviderCookiesRed
     */
    public function testItDeserializesServerRequestCookieParametersRed(string $cookieName, string $cookieValue): void
    {
        $request = (new ServerRequest('get', new Uri('/deserialize-cookies')))
            ->withCookieParams([$cookieName => $cookieValue]);

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();

        $this->expectException(InvalidCookies::class);
        $this->expectExceptionMessage(
            sprintf('Value "%s" for cookie "%s" is invalid for Request [get /deserialize-cookies]', $cookieValue, $cookieName)
        );
        $validator->validate($request);
    }
}
