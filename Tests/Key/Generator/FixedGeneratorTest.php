<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key\Generator;

use Damax\Bundle\ApiAuthBundle\Key\Generator\FixedGenerator;
use PHPUnit\Framework\TestCase;

class FixedGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_key()
    {
        $this->assertEquals('ABC', (new FixedGenerator('ABC'))->generateKey());
    }
}
