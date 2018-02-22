<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Symfony\Component\Security\Core\User\UserInterface;

class Builder implements TokenBuilder
{
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
            switch ($name) {
                case Claims::SUBJECT:
                    $builder->relatedTo($value);
                    break;
                case Claims::ISSUER:
                    $builder->issuedBy($value);
                    break;
                case Claims::AUDIENCE:
                    $builder->permittedFor($value);
                    break;
                case Claims::ISSUED_AT:
                    $builder->issuedAt($value);
                    break;
                case Claims::NOT_BEFORE:
                    $builder->canOnlyBeUsedAfter($value);
                    break;
                case Claims::EXPIRATION_TIME:
                    $builder->expiresAt($value);
                    break;
                case Claims::ID:
                    $builder->identifiedBy($value);
                    break;
                default:
                    $builder->withClaim($name, $value);
            }
        }

        return (string) $builder->getToken($this->config->getSigner(), $this->config->getSigningKey());
    }
}
