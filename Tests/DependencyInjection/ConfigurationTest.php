<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\DependencyInjection;

use Damax\Bundle\ApiAuthBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * @test
     */
    public function it_processes_empty_config()
    {
        $config = [
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'tokens' => [],
        ]);
    }

    /**
     * @test
     */
    public function it_processes_config()
    {
        $config = [
            'tokens' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'tokens' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ]);
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
