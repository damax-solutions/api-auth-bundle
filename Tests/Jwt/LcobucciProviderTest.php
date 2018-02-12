<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt;

use Damax\Bundle\ApiAuthBundle\Jwt\LcobucciProvider;
use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use PHPUnit\Framework\TestCase;

class LcobucciProviderTest extends TestCase
{
    private $provider;

    protected function setUp()
    {
        $config = new Configuration();
        $datetime = new DateTimeImmutable('2018-02-09 06:10:00');
        $this->provider = new LcobucciProvider($config, new FrozenClock($datetime), ['github', 'bitbucket'], 'app');
    }
}
