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
    public function it_creates_response_from_error()
    {
        $response = (new JsonResponseFactory())->fromError(401);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"error":{"code":401,"message":"Unauthorized"}}', $response->getContent());
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_creates_response_from_token()
    {
        $response = (new JsonResponseFactory())->fromToken('__jwt__');
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals('{"token":"__jwt__"}', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
