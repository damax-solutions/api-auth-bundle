<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

final class HeaderExtractor implements Extractor
{
    private $name;
    private $prefix;

    public function __construct(string $name, string $prefix = null)
    {
        $this->name = $name;
        $this->prefix = $prefix;
    }

    public function extractKey(Request $request): ?string
    {
        $value = $request->headers->get($this->name);

        if (!$value || !$this->prefix) {
            return $value;
        }

        if (0 !== strpos($value, $this->prefix . ' ')) {
            return null;
        }

        return substr($value, strlen($this->prefix) + 1);
    }
}
