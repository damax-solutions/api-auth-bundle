<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Storage\InMemoryStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\KeyNotFound;
use PHPUnit\Framework\TestCase;

class InMemoryStorageTest extends TestCase
{
    /**
     * @test
     */
    public function it_checks_key_existence()
    {
        $storage = new InMemoryStorage(['john.doe' => 'ABC', 'jane.doe' => 'XYZ'], 600);

        $this->assertTrue($storage->has('ABC'));
        $this->assertTrue($storage->has('XYZ'));
        $this->assertFalse($storage->has('123'));

        return $storage;
    }

    /**
     * @depends it_checks_key_existence
     *
     * @test
     */
    public function it_retrieves_key(InMemoryStorage $storage)
    {
        $key = $storage->get('ABC');

        $this->assertEquals('ABC', $key->key());
        $this->assertEquals('john.doe', $key->identity());
        $this->assertEquals(600, $key->ttl());

        $key = $storage->get('XYZ');

        $this->assertEquals('XYZ', $key->key());
        $this->assertEquals('jane.doe', $key->identity());
        $this->assertEquals(600, $key->ttl());
    }

    /**
     * @depends it_checks_key_existence
     *
     * @test
     */
    public function it_throws_exception_when_retrieving_missing_key(InMemoryStorage $storage)
    {
        $this->expectException(KeyNotFound::class);

        $storage->get('123');
    }
}
