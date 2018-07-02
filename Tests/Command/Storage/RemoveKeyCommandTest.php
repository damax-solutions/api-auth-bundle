<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Command\Storage;

use Damax\Bundle\ApiAuthBundle\Command\Storage\RemoveKeyCommand;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;

/**
 * @group integration
 * @group console
 */
class RemoveKeyCommandTest extends StorageCommandTestCase
{
    /**
     * @var Storage|MockObject
     */
    private $storage;

    protected function createCommand(): Command
    {
        $this->storage = $this->createMock(Storage::class);

        return new RemoveKeyCommand($this->storage);
    }

    /**
     * @test
     */
    public function it_removes_key()
    {
        $this->storage
            ->expects($this->once())
            ->method('remove')
            ->with('XYZ')
        ;

        $code = $this->tester->execute(['command' => 'damax:api-auth:storage:remove-key', 'key' => 'XYZ']);

        $this->assertSame(0, $code);
        $this->assertEquals('[OK] Done', trim($this->tester->getDisplay()));
    }
}
