<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

final class QueryExtractor implements Extractor
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function extractKey(Request $request): ?string
    {
        return $request->query->get($this->name);
    }
}
