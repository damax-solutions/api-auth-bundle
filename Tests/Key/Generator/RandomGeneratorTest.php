<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key\Generator;

use Damax\Bundle\ApiAuthBundle\Key\Generator\RandomGenerator;
use PHPUnit\Framework\TestCase;

class RandomGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_key()
    {
        $generator = new RandomGenerator();

        $key1 = $generator->generateKey();
        $key2 = $generator->generateKey();

        $this->assertTrue(40 === strlen($key1));
        $this->assertTrue(40 === strlen($key2));
        $this->assertNotEquals($key1, $key2);
    }
}
