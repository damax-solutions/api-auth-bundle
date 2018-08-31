<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Symfony\Component\Security\Core\User\UserInterface;

final class Builder implements TokenBuilder
{
    private const CLAIM_TO_METHOD = [
        Claims::SUBJECT => 'relatedTo',
        Claims::ISSUER => 'issuedBy',
        Claims::AUDIENCE => 'permittedFor',
        Claims::ISSUED_AT => 'issuedAt',
        Claims::NOT_BEFORE => 'canOnlyBeUsedAfter',
        Claims::EXPIRATION_TIME => 'expiresAt',
        Claims::ID => 'identifiedBy',
    ];

    private $config;
    private $claims;

    public function __construct(JwtConfiguration $config, Claims $claims)
    {
        $this->config = $config;
        $this->claims = $claims;
    }

    public function fromUser(UserInterface $user): string
    {
        $builder = $this->config->createBuilder();

        foreach ($this->claims->resolve($user) as $name => $value) {
            $method = self::CLAIM_TO_METHOD[$name] ?? null;

            if ($method) {
                $builder->{$method}($value);
            } else {
                $builder->withClaim($name, $value);
            }
        }

        return (string) $builder->getToken($this->config->getSigner(), $this->config->getSigningKey());
    }
}
