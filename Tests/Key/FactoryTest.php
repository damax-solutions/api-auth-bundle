<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key;

use Damax\Bundle\ApiAuthBundle\Key\Factory;
use Damax\Bundle\ApiAuthBundle\Key\Generator\FixedGenerator;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_key()
    {
        $key = (new Factory(new FixedGenerator('XYZ')))->createKey('john.doe', 600);

        $this->assertEquals('XYZ', $key->key());
        $this->assertEquals('john.doe', $key->username());
        $this->assertEquals(600, $key->ttl());
    }
}
