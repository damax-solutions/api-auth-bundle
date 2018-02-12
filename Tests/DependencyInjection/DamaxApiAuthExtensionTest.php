<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\DependencyInjection;

use Damax\Bundle\ApiAuthBundle\DependencyInjection\DamaxApiAuthExtension;
use Damax\Bundle\ApiAuthBundle\Extractor\ChainExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\CookieExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\HeaderExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\QueryExtractor;
use Damax\Bundle\ApiAuthBundle\Listener\ExceptionListener;
use Damax\Bundle\ApiAuthBundle\Security\ApiKeyAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\UserProvider;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\KernelEvents;

class DamaxApiAuthExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function it_registers_api_key_services()
    {
        $this->load([
            'api_key' => [
                'tokens' => [
                    'foo' => 'bar',
                    'baz' => 'qux',
                ],
                'extractors' => [
                    ['type' => 'header', 'name' => 'X-Authorization', 'prefix' => 'KEY'],
                    ['type' => 'query', 'name' => 'api_key'],
                    ['type' => 'cookie', 'name' => 'api_key'],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('damax.api_auth.api_key.user_provider', UserProvider::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('damax.api_auth.api_key.user_provider', 0, [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertContainerBuilderHasService('damax.api_auth.api_key.authenticator', ApiKeyAuthenticator::class);

        /** @var Definition $extractors */
        $extractors = $this->container
            ->getDefinition('damax.api_auth.api_key.authenticator')
            ->getArgument(0)
        ;
        $this->assertEquals(ChainExtractor::class, $extractors->getClass());

        /** @var Definition[] $definitions */
        $definitions = $extractors->getArgument(0);

        // Header
        $this->assertEquals(HeaderExtractor::class, $definitions[0]->getClass());
        $this->assertEquals('X-Authorization', $definitions[0]->getArgument(0));
        $this->assertEquals('KEY', $definitions[0]->getArgument(1));

        // Query
        $this->assertEquals(QueryExtractor::class, $definitions[1]->getClass());
        $this->assertEquals('api_key', $definitions[1]->getArgument(0));

        // Cookie
        $this->assertEquals(CookieExtractor::class, $definitions[2]->getClass());
        $this->assertEquals('api_key', $definitions[2]->getArgument(0));

        $this->assertContainerBuilderHasServiceDefinitionWithTag(ExceptionListener::class, 'kernel.event_listener', [
            'event' => KernelEvents::EXCEPTION,
            'method' => 'onKernelException',
        ]);
    }

    /**
     * @test
     */
    public function it_registers_exception_formatting()
    {
        $this->load([]);

        $this->assertContainerBuilderHasServiceDefinitionWithTag(ExceptionListener::class, 'kernel.event_listener', [
            'event' => 'kernel.exception',
            'method' => 'onKernelException',
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_register_exception_formatting()
    {
        $this->load(['format_exceptions' => false]);

        $this->assertEmpty($this->container->getDefinition(ExceptionListener::class)->getTags());
    }

    protected function getContainerExtensions(): array
    {
        return [
            new DamaxApiAuthExtension(),
        ];
    }
}
