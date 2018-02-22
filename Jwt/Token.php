<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt;

final class Token
{
    private $data;

    public static function fromClaims(array $claims): self
    {
        return new self($claims);
    }

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    public function get(string $name, $default = null)
    {
        return $this->data[$name] ?? $default;
    }

    public function all(): array
    {
        return $this->data;
    }
}
