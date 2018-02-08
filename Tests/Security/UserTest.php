<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security;

use Damax\Bundle\ApiAuthBundle\Security\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @test
     */
    public function it_retrieves_user_values()
    {
        $user = new User('foo');

        $this->assertEquals('foo', $user->getUsername());
        $this->assertEquals(['ROLE_API'], $user->getRoles());
        $this->assertEquals('', $user->getPassword());
        $this->assertEquals('', $user->getSalt());
    }
}
