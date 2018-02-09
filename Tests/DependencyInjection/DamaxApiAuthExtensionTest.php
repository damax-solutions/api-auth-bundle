<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\DependencyInjection;

use Damax\Bundle\ApiAuthBundle\DependencyInjection\DamaxApiAuthExtension;
use Damax\Bundle\ApiAuthBundle\Listener\ResponseListener;
use Damax\Bundle\ApiAuthBundle\Security\ApiKeyAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\UserProvider;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class DamaxApiAuthExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function it_registers_services()
    {
        $this->load([
            'tokens' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ]);

        $this->assertContainerBuilderHasService(ApiKeyAuthenticator::class);
        $this->assertContainerBuilderHasService(UserProvider::class);
        $this->assertContainerBuilderHasParameter('damax.api_auth.tokens', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithTag(ResponseListener::class, 'kernel.event_listener', [
            'event' => KernelEvents::EXCEPTION,
            'method' => 'onKernelException',
        ]);
    }

    protected function getContainerExtensions(): array
    {
        return [
            new DamaxApiAuthExtension(),
        ];
    }
}
