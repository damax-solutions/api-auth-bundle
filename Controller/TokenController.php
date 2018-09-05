<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Controller;

use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Damax\Bundle\ApiAuthBundle\Security\ResponseFactory;
use Swagger\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenController
{
    private $securityTokenStorage;
    private $responseFactory;

    public function __construct(TokenStorageInterface $securityTokenStorage, ResponseFactory $responseFactory)
    {
        $this->securityTokenStorage = $securityTokenStorage;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @OpenApi\Post(
     *     tags={"security"},
     *     summary="Refresh token.",
     *     security={
     *         {"Bearer"=""}
     *     },
     *     @OpenApi\Response(
     *         response=200,
     *         description="Authentication result.",
     *         @OpenApi\Schema(ref="#/definitions/SecurityLoginResult")
     *     ),
     *     @OpenApi\Response(
     *         response=401,
     *         description="Bad credentials."
     *     )
     * )
     *
     * @Route("/refresh-token", methods={"POST"})
     *
     * @throws UnauthorizedHttpException
     */
    public function refreshAction(TokenBuilder $tokenBuilder): Response
    {
        $user = $this->securityTokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new UnauthorizedHttpException('Bearer');
        }

        $jwtString = $tokenBuilder->fromUser($user);

        return $this->responseFactory->fromToken($jwtString);
    }
}
