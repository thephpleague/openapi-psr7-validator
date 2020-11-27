<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema;

use League\OpenAPIValidation\Schema\BreadCrumb;
use PHPUnit\Framework\TestCase;

final class BreadCrumbTest extends TestCase
{
    public function testItCanBuildChainProperly(): void
    {
        $crumb1 = new BreadCrumb();
        $crumb2 = $crumb1->addCrumb('key1');
        $crumb3 = $crumb2->addCrumb('key2');

        $this->assertSame($crumb3->buildChain(), ['key1', 'key2']);
    }
}
