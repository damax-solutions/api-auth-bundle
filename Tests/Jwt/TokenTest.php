<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt;

use Damax\Bundle\ApiAuthBundle\Jwt\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_from_claims()
    {
        $token = Token::fromClaims(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $token->all());

        return $token;
    }

    /**
     * @depends it_creates_from_claims
     *
     * @test
     */
    public function it_checks_claim_existence(Token $token)
    {
        $this->assertTrue($token->has('foo'));
        $this->assertTrue($token->has('baz'));
        $this->assertFalse($token->has('bar'));
    }

    /**
     * @depends it_creates_from_claims
     *
     * @test
     */
    public function it_retrieves_claim(Token $token)
    {
        $this->assertEquals('bar', $token->get('foo'));
        $this->assertEquals('qux', $token->get('baz'));
        $this->assertNull($token->get('abc'));
        $this->assertEquals('XYZ', $token->get('ABC', 'XYZ'));
    }
}
