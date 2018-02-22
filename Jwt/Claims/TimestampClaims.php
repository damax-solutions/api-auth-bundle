<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Lcobucci\Clock\Clock;
use Symfony\Component\Security\Core\User\UserInterface;

class TimestampClaims implements Claims
{
    private $clock;
    private $ttl;

    public function __construct(Clock $clock, int $ttl)
    {
        $this->clock = $clock;
        $this->ttl = $ttl;
    }

    public function resolve(UserInterface $user): array
    {
        $now = $this->clock->now();

        return [
            self::ISSUED_AT => $now,
            self::NOT_BEFORE => $now,
            self::EXPIRATION_TIME => $now->modify(sprintf('+%d seconds', $this->ttl)),
        ];
    }
}
