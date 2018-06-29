<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Command\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Storage\KeyNotFoundException;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Reader as Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LookupKeyCommand extends Command
{
    protected static $defaultName = 'damax:api-auth:storage:lookup-key';

    private $storage;

    public function __construct(Storage $storage)
    {
        parent::__construct();

        $this->storage = $storage;
    }

    protected function configure()
    {
        $this
            ->setDescription('Lookup api key in storage.')
            ->addArgument('key', InputArgument::REQUIRED, 'Api key.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $key = $this->storage->get($input->getArgument('key'));
        } catch (KeyNotFoundException $e) {
            $io->error('Key not found.');

            return 1;
        }

        $io->writeln('');
        $io->table([], [
            ['Key', (string) $key],
            ['Username', $key->username()],
            ['TTL', $key->ttl()],
        ]);
    }
}
