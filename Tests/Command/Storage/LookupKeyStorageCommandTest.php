<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Command\Storage;

use Damax\Bundle\ApiAuthBundle\Command\Storage\LookupKeyCommand;
use Damax\Bundle\ApiAuthBundle\Key\Key;
use Damax\Bundle\ApiAuthBundle\Key\Storage\KeyNotFoundException;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Storage;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;

/**
 * @group integration
 * @group console
 */
class LookupKeyStorageCommandTest extends StorageCommandTestCase
{
    /**
     * @var Storage|MockObject
     */
    private $storage;

    protected function createCommand(): Command
    {
        $this->storage = $this->createMock(Storage::class);

        return new LookupKeyCommand($this->storage);
    }

    /**
     * @test
     */
    public function it_finds_key()
    {
        $this->storage
            ->expects($this->once())
            ->method('get')
            ->with('XYZ')
            ->willReturn(new Key('XYZ', 'john.doe', 60))
        ;

        $code = $this->tester->execute(['command' => 'damax:api-auth:storage:lookup-key', 'key' => 'XYZ']);

        $output = <<<CONSOLE

 ---------- ---------- 
  Key        XYZ       
  Identity   john.doe  
  TTL        60        
 ---------- ---------- 


CONSOLE;

        $this->assertSame(0, $code);
        $this->assertEquals($output, $this->tester->getDisplay());
    }

    /**
     * @test
     */
    public function it_fails_to_find_key()
    {
        $this->storage
            ->expects($this->once())
            ->method('get')
            ->with('XYZ')
            ->willThrowException(new KeyNotFoundException())
        ;

        $code = $this->tester->execute(['command' => 'damax:api-auth:storage:lookup-key', 'key' => 'XYZ']);

        $this->assertSame(1, $code);
        $this->assertEquals('[ERROR] Key not found.', trim($this->tester->getDisplay()));
    }
}
