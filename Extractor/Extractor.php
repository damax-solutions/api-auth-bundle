<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

interface Extractor
{
    public function extractKey(Request $request): ?string;
}
