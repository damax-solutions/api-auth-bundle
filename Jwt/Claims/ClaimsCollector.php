<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Symfony\Component\Security\Core\User\UserInterface;

final class ClaimsCollector implements Claims
{
    private $items = [];

    public function __construct(iterable $items = [])
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    public function add(Claims $claims): void
    {
        $this->items[] = $claims;
    }

    public function resolve(UserInterface $user): array
    {
        $reduce = function (array $acc, Claims $claims) use ($user): array {
            return array_merge($acc, $claims->resolve($user));
        };

        return array_reduce($this->items, $reduce, []);
    }
}
