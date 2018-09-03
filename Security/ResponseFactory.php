<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

interface ResponseFactory
{
    public function fromError(int $code, AuthenticationException $exception = null): Response;

    public function fromToken(string $token): Response;
}
