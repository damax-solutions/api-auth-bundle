<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use PHPUnit\Framework\TestCase;

class KeyTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_valid_key()
    {
        $key = new Key('XYZ', 'john.doe', 3600);

        $this->assertEquals('XYZ', $key->key());
        $this->assertEquals('john.doe', $key->identity());
        $this->assertEquals(3600, $key->ttl());
        $this->assertFalse($key->expired());
    }

    /**
     * @test
     */
    public function it_creates_expired_key()
    {
        $key = new Key('XYZ', 'john.doe', -30);

        $this->assertEquals(0, $key->ttl());
        $this->assertTrue($key->expired());
    }

    /**
     * @test
     */
    public function it_implements_to_string()
    {
        $this->assertEquals('XYZ', (string) new Key('XYZ', 'john.doe', 3600));
    }
}
