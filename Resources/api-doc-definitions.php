<?php

declare(strict_types=1);

return [
    'SecurityLogin' => [
        'type' => 'object',
        'properties' => [
            'username' => ['type' => 'string'],
            'password' => ['type' => 'string'],
        ],
    ],
    'SecurityLoginResult' => [
        'type' => 'object',
        'properties' => [
            'token' => ['type' => 'string'],
        ],
    ],
];
