<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Functional;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function getCacheDir()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logs';
    }

    public function registerBundles(): array
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }
}
