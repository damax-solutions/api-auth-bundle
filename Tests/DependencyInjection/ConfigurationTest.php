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
            'jwt' => [
                'enabled' => false,
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Bearer'],
                ],
                'builder' => [
                    'ttl' => 3600,
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
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Token'],
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
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Token'],
                    ['type' => 'query', 'name' => 'api_key'],
                    ['type' => 'cookie', 'name' => 'api_key'],
                ],
            ],
        ], 'api_key');
    }

    /**
     * @test
     */
    public function it_processes_basic_jwt_config()
    {
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
                    'signing_key' => 'secret',
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
    public function it_processes_jwt_config()
    {
        $filename = tempnam(sys_get_temp_dir(), 'key_');

        $config = [
            'jwt' => [
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
                    'ttl' => 3600,
                ],
                'extractors' => [
                    ['type' => 'header', 'name' => 'Authorization', 'prefix' => 'Bearer'],
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
    public function it_processes_minimal_jwt_config()
    {
        $config = [
            'jwt' => [
                'signer' => 'secret',
            ],
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

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
