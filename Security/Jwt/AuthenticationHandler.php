<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security\Jwt;

use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    private $builder;

    public function __construct(TokenBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $jwtString = $this->builder->fromUser($token->getUser());

        return JsonResponse::create(['token' => $jwtString]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return JsonResponse::create(['message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }
}
