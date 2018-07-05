<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Controller;

use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenController
{
    private $securityTokenStorage;

    public function __construct(TokenStorageInterface $securityTokenStorage)
    {
        $this->securityTokenStorage = $securityTokenStorage;
    }

    /**
     * @Route("/refresh-token", methods={"GET"})
     *
     * @throws UnauthorizedHttpException
     */
    public function refreshAction(TokenBuilder $tokenBuilder): Response
    {
        $user = $this->securityTokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new UnauthorizedHttpException('Bearer');
        }

        return JsonResponse::create(['token' => $tokenBuilder->fromUser($user)]);
    }
}
