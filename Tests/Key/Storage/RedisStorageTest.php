<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use Damax\Bundle\ApiAuthBundle\Key\Storage\KeyNotFoundException;
use Damax\Bundle\ApiAuthBundle\Key\Storage\RedisStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;

class RedisStorageTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $client;

    /**
     * @var RedisStorage
     */
    private $storage;

    protected function setUp()
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->storage = new RedisStorage($this->client);
    }

    /**
     * @test
     */
    public function it_checks_key_in_storage()
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('__call')
            ->withConsecutive(['exists', ['foo']], ['exists', ['bar']])
            ->willReturn(true, false)
        ;

        $this->assertTrue($this->storage->has('foo'));
        $this->assertFalse($this->storage->has('bar'));
    }

    /**
     * @test
     */
    public function it_removes_key_from_storage()
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('__call')
            ->withConsecutive(['del', [['foo']]], ['del', [['bar']]])
        ;

        $this->storage->remove('foo');
        $this->storage->remove('bar');
    }

    /**
     * @test
     */
    public function it_adds_key_to_storage()
    {
        $this->client
            ->expects($this->once())
            ->method('__call')
            ->with('setex', ['XYZ', 60, 'john.doe'])
        ;

        $this->storage->add(new Key('XYZ', 'john.doe', 60));
    }

    /**
     * @test
     */
    public function it_retrieves_key_from_storage()
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('__call')
            ->withConsecutive(
                ['get', ['XYZ']],
                ['ttl', ['XYZ']]
            )
            ->willReturnOnConsecutiveCalls('john.doe', 60)
        ;

        $key = $this->storage->get('XYZ');

        $this->assertEquals('XYZ', $key->key());
        $this->assertEquals('john.doe', $key->identity());
        $this->assertEquals(60, $key->ttl());
    }

    /**
     * @test
     */
    public function it_fails_retrieving_missing_key()
    {
        $this->client
            ->expects($this->once())
            ->method('__call')
            ->with('get', ['XYZ'])
        ;

        $this->expectException(KeyNotFoundException::class);

        $this->storage->get('XYZ');
    }
}
