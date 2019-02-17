<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Command\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Storage\Writer as Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RemoveKeyCommand extends Command
{
    protected static $defaultName = 'damax:api-auth:storage:remove-key';

    private $storage;

    public function __construct(Storage $storage)
    {
        parent::__construct();

        $this->storage = $storage;
    }

    protected function configure()
    {
        $this
            ->setDescription('Remove api key from storage.')
            ->addArgument('key', InputArgument::REQUIRED, 'Api key.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->storage->remove((string) $input->getArgument('key'));

        (new SymfonyStyle($input, $output))->success('Done');
    }
}
