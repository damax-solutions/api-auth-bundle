<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Extractor;

use Damax\Bundle\ApiAuthBundle\Extractor\HeaderExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class HeaderExtractorTest extends TestCase
{
    /**
     * @test
     */
    public function it_extracts_key_without_prefix()
    {
        $request = new Request();
        $request->headers->set('Authorization', 'XYZ');
        $this->assertEquals('XYZ', (new HeaderExtractor('Authorization'))->extractKey($request));

        $request = new Request();
        $this->assertNull((new HeaderExtractor('Authorization'))->extractKey($request));
    }

    /**
     * @test
     */
    public function it_extracts_key_with_prefix()
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer XYZ');
        $this->assertEquals('XYZ', (new HeaderExtractor('Authorization', 'Bearer'))->extractKey($request));

        $request = new Request();
        $request->headers->set('Authorization', 'XYZ');
        $this->assertNull((new HeaderExtractor('Authorization', 'Bearer'))->extractKey($request));
    }
}
