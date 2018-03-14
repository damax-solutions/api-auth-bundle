<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Extractor;

use Damax\Bundle\ApiAuthBundle\Extractor\ChainExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\Extractor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ChainExtractorTest extends TestCase
{
    /**
     * @var Extractor|MockObject
     */
    private $extractor1;

    /**
     * @var Extractor|MockObject
     */
    private $extractor2;

    /**
     * @var ChainExtractor
     */
    private $extractor;

    protected function setUp()
    {
        $this->extractor1 = $this->createMock(Extractor::class);
        $this->extractor2 = $this->createMock(Extractor::class);
        $this->extractor = new ChainExtractor([$this->extractor1, $this->extractor2]);
    }

    /**
     * @test
     */
    public function it_contains_internal_extractors()
    {
        $this->assertAttributeCount(2, 'extractors', $this->extractor);
    }

    /**
     * @test
     */
    public function it_extracts_key_from_first_extractor()
    {
        $request = new Request();

        $this->extractor1
            ->expects($this->once())
            ->method('extractKey')
            ->with($this->identicalTo($request))
            ->willReturn('XYZ')
        ;
        $this->extractor2
            ->expects($this->never())
            ->method('extractKey')
        ;

        $this->assertEquals('XYZ', $this->extractor->extractKey($request));
    }

    /**
     * @test
     */
    public function it_extracts_key_from_second_extractor()
    {
        $request = new Request();

        $this->extractor1
            ->expects($this->once())
            ->method('extractKey')
            ->with($this->identicalTo($request))

        ;
        $this->extractor2
            ->expects($this->once())
            ->method('extractKey')
            ->with($this->identicalTo($request))
            ->willReturn('XYZ')
        ;

        $this->assertEquals('XYZ', $this->extractor->extractKey($request));
    }

    /**
     * @test
     */
    public function it_has_no_key_to_extract()
    {
        $request = new Request();

        $this->extractor1
            ->expects($this->once())
            ->method('extractKey')
            ->with($this->identicalTo($request))

        ;
        $this->extractor2
            ->expects($this->once())
            ->method('extractKey')
            ->with($this->identicalTo($request))
        ;

        $this->assertNull($this->extractor->extractKey($request));
    }
}
