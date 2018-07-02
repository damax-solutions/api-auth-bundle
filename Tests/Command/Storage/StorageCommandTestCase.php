<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Command\Storage;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class StorageCommandTestCase extends KernelTestCase
{
    /**
     * @var CommandTester
     */
    protected $tester;

    protected function setUp()
    {
        static::bootKernel();

        $command = $this->createCommand();
        $command->setApplication(new Application(self::$kernel));

        $this->tester = new CommandTester($command);
    }

    abstract protected function createCommand(): Command;
}
