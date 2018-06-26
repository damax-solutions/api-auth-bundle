<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SecurityController
{
    /**
     * @Method("POST")
     * @Route("/login", name="security_login")
     */
    public function loginAction()
    {
    }
}
