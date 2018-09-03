<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security;

use Damax\Bundle\ApiAuthBundle\Security\JsonResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_error_response()
    {
        $response = (new JsonResponseFactory())->fromError(401);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"error":{"code":401,"message":"Unauthorized"}}', $response->getContent());
    }
}
