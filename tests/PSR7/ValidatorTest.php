<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 10 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use Cache\Adapter\PHPArray\ArrayCachePool;
use OpenAPIValidation\PSR7\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{

    public function test_it_caches_parsed_openapi_spec_green()
    {
        # configure cache
        $pool  = [];
        $cache = new ArrayCachePool(10, $pool);

        # prepare tmp file with OAS
        $oasFile = tempnam(sys_get_temp_dir(), 'openapi_test_');
        copy(__DIR__ . "/../stubs/uber.yaml", $oasFile);

        # parse file
        $v1 = MockValidator::fromYamlFile($oasFile, $cache);

        # drop oas file contents and read again
        file_put_contents($oasFile, "rubbish");
        $v2 = MockValidator::fromYamlFile($oasFile, $cache);

        $this->addToAssertionCount(1);
    }
}

class MockValidator extends Validator
{
}
