<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class JsonResponseFactory implements ResponseFactory
{
    public function createError(int $code): Response
    {
        $error = ['code' => $code, 'message' => Response::$statusTexts[$code]];

        return JsonResponse::create(['error' => $error], $code);
    }
}
