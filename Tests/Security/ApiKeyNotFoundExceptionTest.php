<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security;

use Damax\Bundle\ApiAuthBundle\Security\ApiKeyNotFoundException;
use PHPUnit\Framework\TestCase;

class ApiKeyNotFoundExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function it_verifies_message_key()
    {
        $this->assertEquals('Api key could not be found.', (new ApiKeyNotFoundException())->getMessageKey());
    }
}
