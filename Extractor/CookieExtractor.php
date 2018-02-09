<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

class CookieExtractor implements Extractor
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function extractKey(Request $request): ?string
    {
        return $request->cookies->get($this->name);
    }
}
