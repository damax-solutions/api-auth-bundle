<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security\ApiKey;

use Damax\Bundle\ApiAuthBundle\Security\ApiKey\InvalidApiKey;
use PHPUnit\Framework\TestCase;

class InvalidApiKeyExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function it_verifies_message_key()
    {
        $this->assertEquals('Invalid api key.', (new InvalidApiKey())->getMessageKey());
    }
}
