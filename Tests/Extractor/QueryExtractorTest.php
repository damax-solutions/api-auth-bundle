<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Extractor;

use Damax\Bundle\ApiAuthBundle\Extractor\QueryExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class QueryExtractorTest extends TestCase
{
    /**
     * @test
     */
    public function it_extracts_key()
    {
        $request = new Request(['api_key' => 'XYZ']);
        $this->assertEquals('XYZ', (new QueryExtractor('api_key'))->extractKey($request));

        $request = new Request();
        $this->assertNull((new QueryExtractor('api_key'))->extractKey($request));
    }
}
