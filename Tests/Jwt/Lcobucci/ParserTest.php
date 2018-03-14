<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt\Lcobucci;

use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Parser;
use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Lcobucci\JWT\Parser as JwtParser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var JwtParser|MockObject
     */
    private $jwtParser;

    /**
     * @var Validator|MockObject
     */
    private $validator;

    /**
     * @var Signer\Key
     */
    private $key;

    /**
     * @var FrozenClock
     */
    private $clock;

    /**
     * @var Parser
     */
    private $parser;

    protected function setUp()
    {
        $this->signer = new Signer\None();
        $this->jwtParser = $this->createMock(JwtParser::class);
        $this->validator = $this->createMock(Validator::class);
        $this->key = new Signer\Key('');

        $config = JwtConfiguration::forSymmetricSigner($this->signer, $this->key);
        $config->setParser($this->jwtParser);
        $config->setValidator($this->validator);

        $this->clock = new FrozenClock(new DateTimeImmutable('2018-02-09 06:10:00'));
        $this->parser = new Parser($config, $this->clock, ['github', 'bitbucket'], 'app');
    }

    /**
     * @test
     */
    public function it_validates_jwt_string()
    {
        /** @var Token $token */
        $token = $this->createMock(Token::class);

        $this->jwtParser
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

        $this->assertTrue($this->parser->isValid('XYZ'));
    }

    /**
     * @test
     */
    public function it_parses_jwt_string()
    {
        $claims = new Token\DataSet(['foo' => 'bar', 'baz' => 'qux'], '');

        $jwtToken = new Token\Plain(new Token\DataSet([], ''), $claims, Token\Signature::fromEmptyData());

        $this->jwtParser
            ->expects($this->once())
            ->method('parse')
            ->with('XYZ')
            ->willReturn($jwtToken)
        ;

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $this->parser->parse('XYZ')->all());
    }
}
