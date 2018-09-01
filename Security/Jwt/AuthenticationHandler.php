<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security\Jwt;

use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Damax\Bundle\ApiAuthBundle\Security\ResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    private $builder;
    private $response;

    public function __construct(TokenBuilder $builder, ResponseFactory $response)
    {
        $this->builder = $builder;
        $this->response = $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException('User not supported.');
        }

        $jwtString = $this->builder->fromUser($user);

        return JsonResponse::create(['token' => $jwtString]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->response->createError(Response::HTTP_UNAUTHORIZED);
    }
}
