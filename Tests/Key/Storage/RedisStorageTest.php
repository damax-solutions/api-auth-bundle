<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use Damax\Bundle\ApiAuthBundle\Key\Storage\KeyNotFound;
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
        $this->storage = new RedisStorage($this->client, 'api_');
    }

    /**
     * @test
     */
    public function it_checks_key_in_storage()
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('__call')
            ->withConsecutive(['exists', ['api_foo']], ['exists', ['api_bar']])
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
            ->withConsecutive(
                ['del', [['api_foo']]],
                ['del', [['api_bar']]]
            )
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
            ->with('setex', ['api_XYZ', 60, 'john.doe'])
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
                ['get', ['api_XYZ']],
                ['ttl', ['api_XYZ']]
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
            ->with('get', ['api_XYZ'])
        ;

        $this->expectException(KeyNotFound::class);

        $this->storage->get('XYZ');
    }
}
