<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Request;

use Damax\Bundle\ApiAuthBundle\Request\RequestMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestMatcherTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider provideRequestData
     */
    public function it_matches_request(string $baseUrl, string $url, bool $result)
    {
        $this->assertEquals($result, (new RequestMatcher($baseUrl))->matches(Request::create($url)));
    }

    public function provideRequestData(): array
    {
        return [
            ['/', '/foo', true],
            ['/foo', '/bar', false],
            ['/api', '/ap', false],
            ['/api', '/api', true],
            ['/api', '/api/', true],
            ['/api', '/api/foo', true],
        ];
    }
}
