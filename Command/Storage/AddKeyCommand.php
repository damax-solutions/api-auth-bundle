<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Command\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Factory;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Writer as Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AddKeyCommand extends Command
{
    protected static $defaultName = 'damax:api-auth:storage:add-key';

    private $factory;
    private $storage;

    public function __construct(Factory $factory, Storage $storage)
    {
        parent::__construct();

        $this->factory = $factory;
        $this->storage = $storage;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add api key to storage.')
            ->addArgument('identity', InputArgument::REQUIRED, 'Identity of the key.')
            ->addArgument('ttl', InputArgument::OPTIONAL, 'Time to live in free form.', '1 week')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (false === $ttl = strtotime($input->getArgument('ttl'), 0)) {
            $io->error('Invalid ttl.');

            return 1;
        }

        $key = $this->factory->createKey($input->getArgument('identity'), $ttl);

        $this->storage->add($key);

        $io->success('Key: ' . (string) $key);
    }
}
