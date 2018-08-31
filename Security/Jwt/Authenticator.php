<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security\Jwt;

use Damax\Bundle\ApiAuthBundle\Extractor\Extractor;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Damax\Bundle\ApiAuthBundle\Jwt\TokenParser;
use Damax\Bundle\ApiAuthBundle\Security\AbstractAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class Authenticator extends AbstractAuthenticator
{
    private $tokenParser;
    private $identityClaim;

    public function __construct(Extractor $extractor, TokenParser $tokenParser, string $identityClaim = null)
    {
        parent::__construct($extractor);

        $this->tokenParser = $tokenParser;
        $this->identityClaim = $identityClaim;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->tokenParser->isValid($credentials);
    }

    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        $jwtToken = $this->tokenParser->parse($credentials);

        if (null === $username = $jwtToken->get($this->identityClaim ?? Claims::SUBJECT)) {
            throw new AuthenticationException('Username could not be identified.');
        }

        return $userProvider->loadUserByUsername($username);
    }
}
