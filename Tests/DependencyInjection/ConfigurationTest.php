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
        $config = [];

        $this->assertProcessedConfigurationEquals([$config], [
            'api_key' => [
                'enabled' => false,
                'tokens' => [],
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Token'],
                ],
            ],
            'format_exceptions' => true,
        ]);
    }

    /**
     * @test
     */
    public function it_processes_simplified_api_key_config()
    {
        $config = [
            'api_key' => ['foo' => 'bar', 'baz' => 'qux'],
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'api_key' => [
                'enabled' => true,
                'tokens' => ['foo' => 'bar', 'baz' => 'qux'],
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Token'],
                ],
            ],
        ], 'api_key');
    }

    /**
     * @test
     */
    public function it_processes_api_key_config()
    {
        $config = [
            'api_key' => [
                'tokens' => ['foo' => 'bar', 'baz' => 'qux'],
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization'],
                    ['type' => 'query', 'name' => 'api_key'],
                    ['type' => 'cookie', 'name' => 'api_key'],
                ],
            ],
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'api_key' => [
                'enabled' => true,
                'tokens' => ['foo' => 'bar', 'baz' => 'qux'],
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization'],
                    ['type' => 'query', 'name' => 'api_key'],
                    ['type' => 'cookie', 'name' => 'api_key'],
                ],
            ],
        ], 'api_key');
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
