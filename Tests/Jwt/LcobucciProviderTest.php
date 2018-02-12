<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt;

use Damax\Bundle\ApiAuthBundle\Jwt\LcobucciProvider;
use DateTimeImmutable;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validator;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class LcobucciProviderTest extends TestCase
{
    /**
     * @var Signer|PHPUnit_Framework_MockObject_MockObject
     */
    private $signer;

    /**
     * @var Parser|PHPUnit_Framework_MockObject_MockObject
     */
    private $parser;

    /**
     * @var Validator|PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var Signer\Key
     */
    private $key;

    /**
     * @var Clock|PHPUnit_Framework_MockObject_MockObject
     */
    private $clock;

    /**
     * @var LcobucciProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->signer = $this->createMock(Signer::class);
        $this->parser = $this->createMock(Parser::class);
        $this->validator = $this->createMock(Validator::class);
        $this->key = new Signer\Key('');

        $config = Configuration::forSymmetricSigner($this->signer, $this->key);
        $config->setParser($this->parser);
        $config->setValidator($this->validator);

        $this->clock = new FrozenClock(new DateTimeImmutable('2018-02-09 06:10:00'));
        $this->provider = new LcobucciProvider($config, $this->clock, ['github', 'bitbucket'], 'app');
    }

    /**
     * @test
     */
    public function it_validates_jwt_string()
    {
        /** @var Token $token */
        $token = $this->createMock(Token::class);

        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with('XYZ')
            ->willReturn($token)
        ;
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturnCallback(function (Token $jwtToken, ...$constraints) use ($token) {
                $this->assertSame($token, $jwtToken);
                $this->assertCount(4, $constraints);
                $this->assertContainsOnlyInstancesOf(Constraint::class, $constraints);

                $constraint = $constraints[0];
                $this->assertInstanceOf(Constraint\ValidAt::class, $constraint);
                $this->assertAttributeSame($this->clock, 'clock', $constraint);

                $constraint = $constraints[1];
                $this->assertInstanceOf(Constraint\SignedWith::class, $constraint);
                $this->assertAttributeSame($this->signer, 'signer', $constraint);
                $this->assertAttributeSame($this->key, 'key', $constraint);

                $constraint = $constraints[2];
                $this->assertInstanceOf(Constraint\IssuedBy::class, $constraint);
                $this->assertAttributeEquals(['github', 'bitbucket'], 'issuers', $constraint);

                $constraint = $constraints[3];
                $this->assertInstanceOf(Constraint\PermittedFor::class, $constraint);
                $this->assertAttributeEquals('app', 'audience', $constraint);

                return true;
            })
        ;

        $this->assertTrue($this->provider->isValid('XYZ'));
    }

    /**
     * @test
     */
    public function it_parses_jwt_string()
    {
        $claims = new Token\DataSet(['foo' => 'bar', 'baz' => 'qux'], '');

        $jwtToken = new Token\Plain(new Token\DataSet([], ''), $claims, Token\Signature::fromEmptyData());

        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with('XYZ')
            ->willReturn($jwtToken)
        ;

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $this->provider->parse('XYZ')->all());
    }
}
