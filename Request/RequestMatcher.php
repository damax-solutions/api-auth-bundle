<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class RequestMatcher implements RequestMatcherInterface
{
    private $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function matches(Request $request): bool
    {
        return 0 === strpos($request->getPathInfo(), $this->baseUrl);
    }
}
