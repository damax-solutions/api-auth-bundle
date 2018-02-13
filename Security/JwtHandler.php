<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Builder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class JwtHandler implements AuthenticationSuccessHandlerInterface
{
    private $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $jwtString = $this->builder->fromUser($token->getUser());

        return JsonResponse::create(['token' => $jwtString]);
    }
}
