<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class JsonResponseFactory implements ResponseFactory
{
    public function fromError(int $code, AuthenticationException $exception = null): Response
    {
        $error = ['code' => $code, 'message' => Response::$statusTexts[$code]];

        return JsonResponse::create(['error' => $error], $code);
    }

    public function fromToken(string $token): Response
    {
        return JsonResponse::create(['token' => $token]);
    }
}
