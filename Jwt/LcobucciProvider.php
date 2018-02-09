<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt;

use Lcobucci\Clock\Clock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token as JwtToken;
use Lcobucci\JWT\Validation\Constraint;

class LcobucciProvider implements TokenParser
{
    private $config;
    private $clock;

    /**
     * @var Constraint[]
     */
    private $constraints;

    public function __construct(Configuration $config, Clock $clock)
    {
        $this->config = $config;
        $this->clock = $clock;
    }

    public function addConstraint(Constraint $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    public function isValid(string $jwt): bool
    {
        return $this->config->getValidator()->validate($this->parseJwt($jwt), $this->constraints);
    }

    public function parse(string $jwt): Token
    {
        return Token::fromClaims($this->parseJwt($jwt)->claims()->all());
    }

    private function parseJwt(string $jwt): JwtToken
    {
        return $this->config->getParser()->parse($jwt);
    }
}
