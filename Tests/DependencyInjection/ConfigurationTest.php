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
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Token'],
                ],
                'generator' => 'random',
                'storage' => [],
            ],
            'jwt' => [
                'enabled' => false,
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Bearer'],
                ],
                'builder' => [
                    'ttl' => 3600,
                ],
            ],
            'format_exceptions' => [
                'enabled' => false,
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_processes_simplified_api_key_config()
    {
        $config = [
            'api_key' => [
                'storage' => ['foo' => 'bar', 'baz' => 'qux'],
            ],
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'api_key' => [
                'enabled' => true,
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Token'],
                ],
                'generator' => 'random',
                'storage' => [
                    [
                        'type' => 'fixed',
                        'tokens' => ['foo' => 'bar', 'baz' => 'qux'],
                        'writable' => false,
                        'table_name' => 'api_key',
                        'redis_client_id' => 'snc_redis.default',
                        'doctrine_connection_id' => 'database_connection',
                    ],
                ],
            ],
        ], 'api_key');
    }

    /**
     * @test
     */
    public function it_configures_api_key()
    {
        $config = [
            'api_key' => [
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Token'],
                    ['type' => 'query', 'name' => 'api_key'],
                    ['type' => 'cookie', 'name' => 'api_key'],
                ],
                'generator' => 'fixed',
                'storage' => [
                    [
                        'type' => 'fixed',
                        'tokens' => ['foo' => 'bar', 'baz' => 'qux'],
                    ],
                    [
                        'type' => 'redis',
                        'writable' => true,
                        'redis_client_id' => 'redis_service_id',
                    ],
                    [
                        'type' => 'doctrine',
                        'writable' => false,
                        'doctrine_connection_id' => 'doctrine_connection',
                        'table_name' => 'keys',
                        'fields' => ['key' => 'id', 'identity' => 'email', 'ttl' => 'expires_at'],
                    ],
                ],
            ],
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'api_key' => [
                'enabled' => true,
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Token'],
                    ['type' => 'query', 'name' => 'api_key'],
                    ['type' => 'cookie', 'name' => 'api_key'],
                ],
                'generator' => 'fixed',
                'storage' => [
                    [
                        'type' => 'fixed',
                        'tokens' => ['foo' => 'bar', 'baz' => 'qux'],
                        'writable' => false,
                        'redis_client_id' => 'snc_redis.default',
                        'doctrine_connection_id' => 'database_connection',
                        'table_name' => 'api_key',
                    ],
                    [
                        'type' => 'redis',
                        'tokens' => [],
                        'writable' => true,
                        'redis_client_id' => 'redis_service_id',
                        'doctrine_connection_id' => 'database_connection',
                        'table_name' => 'api_key',
                    ],
                    [
                        'type' => 'doctrine',
                        'tokens' => [],
                        'writable' => false,
                        'redis_client_id' => 'snc_redis.default',
                        'doctrine_connection_id' => 'doctrine_connection',
                        'table_name' => 'keys',
                        'fields' => ['key' => 'id', 'identity' => 'email', 'ttl' => 'expires_at'],
                    ],
                ],
            ],
        ], 'api_key');
    }

    /**
     * @test
     */
    public function it_requires_verification_key_for_asymmetric_signer()
    {
        $config = [
            'jwt' => [
                'signer' => [
                    'type' => 'asymmetric',
                    'signing_key' => 'secret',
                ],
            ],
        ];

        $this->assertPartialConfigurationIsInvalid([$config], 'jwt', 'Verification key must be specified for "asymmetric" signer.');
    }

    /**
     * @test
     */
    public function it_requires_hmac_algorithm_for_symmetric_signer()
    {
        $config = [
            'jwt' => [
                'signer' => [
                    'type' => 'symmetric',
                    'algorithm' => 'RS256',
                    'signing_key' => 'secret',
                ],
            ],
        ];

        $this->assertPartialConfigurationIsInvalid([$config], 'jwt', 'HMAC algorithm must be specified for "symmetric" signer.');
    }

    /**
     * @test
     */
    public function it_requires_rsa_algorithm_for_asymmetric_signer()
    {
        $config = [
            'jwt' => [
                'signer' => [
                    'type' => 'asymmetric',
                    'algorithm' => 'HS256',
                    'signing_key' => 'signing_secret',
                    'verification_key' => 'verification_secret',
                ],
            ],
        ];

        $this->assertPartialConfigurationIsInvalid([$config], 'jwt', 'RSA or ECDSA algorithm must be specified for "asymmetric" signer.');
    }

    /**
     * @test
     */
    public function it_requires_readable_signing_and_verification_key()
    {
        $config = [
            'jwt' => [
                'signer' => [
                    'type' => 'asymmetric',
                    'algorithm' => 'RS256',
                    'signing_key' => 'signing_secret',
                    'verification_key' => 'verification_secret',
                ],
            ],
        ];

        $this->assertPartialConfigurationIsInvalid([$config], 'jwt', 'Signing and/or verification key is not readable.');
    }

    /**
     * @test
     */
    public function it_requires_no_verification_key_for_symmetric_signer()
    {
        $config = [
            'jwt' => [
                'signer' => [
                    'signing_key' => 'signing_secret',
                    'verification_key' => 'verification_secret',
                ],
            ],
        ];

        $this->assertPartialConfigurationIsInvalid([$config], 'jwt', 'Verification key must no be specified for "symmetric" signer.');
    }

    /**
     * @test
     */
    public function it_requires_no_passphrase_for_symmetric_signer()
    {
        $config = [
            'jwt' => [
                'signer' => [
                    'signing_key' => 'signing_secret',
                    'passphrase' => '__XYZ__',
                ],
            ],
        ];

        $this->assertPartialConfigurationIsInvalid([$config], 'jwt', 'Passphrase must not be specified for "symmetric" signer.');
    }

    /**
     * @test
     */
    public function it_processes_simplified_jwt_config()
    {
        $config = [
            'jwt' => 'secret',
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'jwt' => [
                'enabled' => true,
                'builder' => [
                    'ttl' => 3600,
                ],
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Bearer'],
                ],
                'signer' => [
                    'type' => 'symmetric',
                    'algorithm' => 'HS256',
                    'signing_key' => 'secret',
                    'passphrase' => '',
                ],
            ],
        ], 'jwt');
    }

    /**
     * @test
     */
    public function it_configures_jwt()
    {
        $filename = tempnam(sys_get_temp_dir(), 'key_');

        $config = [
            'jwt' => [
                'builder' => [
                    'issuer' => 'damax-api-auth-bundle',
                    'audience' => 'symfony',
                    'ttl' => 600,
                ],
                'parser' => [
                    'issuers' => ['symfony', 'zend'],
                    'audience' => 'zend',
                ],
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Bearer'],
                    ['type' => 'query', 'name' => 'token'],
                    ['type' => 'cookie', 'name' => 'token'],
                ],
                'signer' => [
                    'type' => 'asymmetric',
                    'signing_key' => $filename,
                    'verification_key' => $filename,
                ],
            ],
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'jwt' => [
                'enabled' => true,
                'builder' => [
                    'issuer' => 'damax-api-auth-bundle',
                    'audience' => 'symfony',
                    'ttl' => 600,
                ],
                'parser' => [
                    'issuers' => ['symfony', 'zend'],
                    'audience' => 'zend',
                ],
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Bearer'],
                    ['type' => 'query', 'name' => 'token'],
                    ['type' => 'cookie', 'name' => 'token'],
                ],
                'signer' => [
                    'type' => 'asymmetric',
                    'algorithm' => 'RS256',
                    'signing_key' => 'file://' . $filename,
                    'verification_key' => 'file://' . $filename,
                    'passphrase' => '',
                ],
            ],
        ], 'jwt');

        unlink($filename);
    }

    /**
     * @test
     */
    public function it_processes_simplified_exceptions_config()
    {
        $config = [
            'format_exceptions' => '/api',
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'format_exceptions' => [
                'enabled' => true,
                'path' => '/api',
            ],
        ], 'format_exceptions');
    }

    /**
     * @test
     */
    public function it_configures_exceptions_formatting()
    {
        $config = [
            'format_exceptions' => [
                'path' => '/api',
            ],
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'format_exceptions' => [
                'enabled' => true,
                'path' => '/api',
            ],
        ], 'format_exceptions');
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
