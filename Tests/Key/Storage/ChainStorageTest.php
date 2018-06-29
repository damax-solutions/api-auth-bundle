<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Storage\ChainStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\FixedStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\KeyNotFoundException;
use PHPUnit\Framework\TestCase;

class ChainStorageTest extends TestCase
{
    /**
     * @var ChainStorage
     */
    private $storage;

    protected function setUp()
    {
        $this->storage = new ChainStorage([
            new FixedStorage(['john.doe' => 'ABC'], 60),
            new FixedStorage(['jane.doe' => 'XYZ'], 90),
        ]);
    }

    /**
     * @test
     */
    public function it_checks_key_existence()
    {
        $this->assertTrue($this->storage->has('ABC'));
        $this->assertTrue($this->storage->has('XYZ'));
        $this->assertFalse($this->storage->has('123'));
    }

    /**
     * @test
     */
    public function it_retrieves_key()
    {
        $key = $this->storage->get('ABC');

        $this->assertEquals('ABC', $key->key());
        $this->assertEquals('john.doe', $key->identity());
        $this->assertEquals(60, $key->ttl());

        $key = $this->storage->get('XYZ');

        $this->assertEquals('XYZ', $key->key());
        $this->assertEquals('jane.doe', $key->identity());
        $this->assertEquals(90, $key->ttl());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_retrieving_missing_key()
    {
        $this->expectException(KeyNotFoundException::class);

        $this->storage->get('123');
    }
}
