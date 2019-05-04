<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 04 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\Schema;

use OpenAPIValidation\Schema\BreadCrumb;
use PHPUnit\Framework\TestCase;

class BreadCrumbTest extends TestCase
{

    public function test_it_can_build_chain_properly()
    {
        $crumb1 = new BreadCrumb();
        $crumb2 = $crumb1->addCrumb("key1");
        $crumb3 = $crumb2->addCrumb("key2");


        $this->assertTrue(["key1", "key2"] === $crumb3->buildChain());
    }
}
