<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Symfony\Component\HttpFoundation\Response;

interface ResponseFactory
{
    public function createError(int $code): Response;
}
