<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Command\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Factory;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Writer as Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddKeyCommand extends Command
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
        // 10 years.
        $ttl = 3600 * 24 * 365 * 10;

        $this
            ->setDescription('Add api key to storage.')
            ->addArgument('username', InputArgument::REQUIRED, 'Username for the key.')
            ->addOption('ttl', null, InputOption::VALUE_REQUIRED, 'Time to live in seconds.', $ttl)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $this->factory->createKey($input->getArgument('username'), (int) $input->getOption('ttl'));

        $this->storage->add($key);

        (new SymfonyStyle($input, $output))->success('Key: ' . (string) $key);
    }
}
