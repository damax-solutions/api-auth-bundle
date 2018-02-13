<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci;

use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Lcobucci\Clock\Clock;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class Builder implements TokenBuilder
{
    private $config;
    private $clock;
    private $ttl;
    private $issuer;
    private $audience;

    public function __construct(JwtConfiguration $config, Clock $clock, int $ttl, string $issuer = null, string $audience = null)
    {
        $this->config = $config;
        $this->clock = $clock;
        $this->ttl = $ttl;
        $this->issuer = $issuer;
        $this->audience = $audience;
    }

    public function fromUser(UserInterface $user): string
    {
        $now = $this->clock->now();

        $builder = $this->config
            ->createBuilder()
            ->relatedTo($user->getUsername())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify(sprintf('+%d seconds', $this->ttl)))
            ->withClaim('roles', array_map([$this, 'roleToString'], $user->getRoles()))
        ;

        if ($this->issuer) {
            $builder->issuedBy($this->issuer);
        }

        if ($this->audience) {
            $builder->permittedFor($this->audience);
        }

        return (string) $builder->getToken($this->config->getSigner(), $this->config->getSigningKey());
    }

    private function roleToString($role): string
    {
        $str = strtolower(strval($role instanceof Role ? $role->getRole() : $role));

        return 0 === strpos($str, 'role_') ? substr($str, 5) : $str;
    }
}
