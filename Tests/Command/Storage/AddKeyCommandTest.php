<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Command\Storage;

use Damax\Bundle\ApiAuthBundle\Command\Storage\AddKeyCommand;
use Damax\Bundle\ApiAuthBundle\Key\Factory;
use Damax\Bundle\ApiAuthBundle\Key\Generator\FixedGenerator;
use Damax\Bundle\ApiAuthBundle\Key\Key;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;

/**
 * @group integration
 * @group console
 */
class AddKeyCommandTest extends StorageCommandTestCase
{
    /**
     * @var Storage|MockObject
     */
    private $storage;

    protected function createCommand(): Command
    {
        $this->storage = $this->createMock(Storage::class);

        return new AddKeyCommand(new Factory(new FixedGenerator('XYZ')), $this->storage);
    }

    /**
     * @test
     */
    public function it_adds_key()
    {
        /** @var Key $key */
        $key = null;

        $this->storage
            ->expects($this->once())
            ->method('add')
            ->willReturnCallback(function (Key $result) use (&$key) {
                $key = $result;
            })
        ;

        $code = $this->tester->execute(['command' => 'damax:api-auth:storage:add-key', 'identity' => 'john.doe', 'ttl' => '2hours']);

        $this->assertSame(0, $code);
        $this->assertEquals('[OK] Key: XYZ', trim($this->tester->getDisplay()));

        $this->assertEquals('XYZ', $key->key());
        $this->assertEquals('john.doe', $key->identity());
        $this->assertEquals(7200, $key->ttl());
    }

    /**
     * @test
     */
    public function it_fails_to_add_key()
    {
        $code = $this->tester->execute(['command' => 'damax:api-auth:storage:add-key', 'identity' => 'john.doe', 'ttl' => 'invalid']);

        $this->assertSame(1, $code);
        $this->assertEquals('[ERROR] Invalid ttl.', trim($this->tester->getDisplay()));
    }
}
