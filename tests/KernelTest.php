<?php

namespace App\Tests;

use App\Kernel;
use PHPUnit\Framework\TestCase;

class KernelTest extends TestCase
{

    /**
     * Build Test.
     */
    public function testBuild()
    {
        $kernel = new Kernel('dev', true);
        $kernel->boot();

        $this->assertNotEmpty($kernel->getBundles());
    }
}
