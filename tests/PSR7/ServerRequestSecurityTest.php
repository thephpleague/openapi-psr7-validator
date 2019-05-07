<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use cebe\openapi\Reader;
use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\Exception\Request\Security\NoRequestSecurityApiKey;
use OpenAPIValidation\PSR7\ServerRequestValidator;

class ServerRequestSecurityTest extends BaseValidatorTest
{
    protected $apiSpecFile = __DIR__ . "/../stubs/uber.yaml";

    function test_request_green()
    {
        $request = (new ServerRequest("get", "/products"))
            ->withQueryParams([
                'server_token' => 'key value',
                'latitude'     => '20.22',
                'longitude'    => '30.84',
            ]);

        $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_security_api_token_red()
    {
        $request = (new ServerRequest("get", "/products"))->withQueryParams([
            'server_token' => 'key value',
            'latitude'     => '20.22',
            'longitude'    => '30.84',
        ]);


        try {
            $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($request);
            #$this->fail('Exception expected');
        } catch (NoRequestSecurityApiKey $e) {
            $this->assertEquals('server_token', $e->apiKeyName());
            $this->assertEquals('query', $e->apiKeyLocation());
            $this->assertEquals('/products', $e->addr()->path());
            $this->assertEquals('get', $e->addr()->method());
        }
    }


}
