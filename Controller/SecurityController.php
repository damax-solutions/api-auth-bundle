<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;

class SecurityController
{
    /**
     * @Route("/login", methods={"POST"}, name="security_login")
     */
    public function loginAction()
    {
    }
}
