<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Controller;

use Swagger\Annotations as OpenApi;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController
{
    /**
     * @OpenApi\Post(
     *     tags={"security"},
     *     summary="User login.",
     *     @OpenApi\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         @OpenApi\Schema(ref="#/definitions/SecurityLogin")
     *     ),
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
     * @Route("/login", methods={"POST"}, name="security_login")
     */
    public function loginAction()
    {
    }
}
