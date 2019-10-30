<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

final class HeadersTest extends BaseValidatorTest
{
    public function testItValidatesRequestQueryArgumentsGreen() : void
    {
        $request = (new ServerRequest('get', new Uri('/path1?queryArgA=20')))->withHeader('header-a', 'value A');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }
}
