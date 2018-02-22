<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt;

use Lcobucci\JWT\Token\RegisteredClaims;
use Symfony\Component\Security\Core\User\UserInterface;

interface Claims extends RegisteredClaims
{
    // https://www.iana.org/assignments/jwt/jwt.xhtml
    const FULL_NAME = 'name';
    const FIRST_NAME = 'given_name';
    const LAST_NAME = 'family_name';
    const MIDDLE_NAME = 'middle_name';
    const NICKNAME = 'nickname';
    const GENDER = 'gender';
    const BIRTH_DATE = 'birthdate';
    const LOCALE = 'locale';
    const TIMEZONE = 'zoneinfo';
    const EMAIL = 'email';
    const EMAIL_VERIFIED = 'email_verified';
    const PHONE_NUMBER = 'phone_number';
    const PHONE_NUMBER_VERIFIED = 'phone_number_verified';
    const ADDRESS = 'address';

    const ROLES = 'roles';

    public function resolve(UserInterface $user): array;
}
