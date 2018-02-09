<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Extractor;

use Damax\Bundle\ApiAuthBundle\Extractor\CookieExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CookieExtractorTest extends TestCase
{
    /**
     * @test
     */
    public function it_extracts_key()
    {
        $request = new Request([], [], [], ['api_key' => 'XYZ']);
        $this->assertEquals('XYZ', (new CookieExtractor('api_key'))->extractKey($request));

        $request = new Request();
        $this->assertNull((new CookieExtractor('api_key'))->extractKey($request));
    }
}
