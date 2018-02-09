<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

class ChainExtractor implements Extractor
{
    /**
     * @var Extractor[]
     */
    private $extractors = [];

    public function __construct(array $extractors = [])
    {
        foreach ($extractors as $extractor) {
            $this->add($extractor);
        }
    }

    public function add(Extractor $extractor): void
    {
        $this->extractors[] = $extractor;
    }

    public function extractKey(Request $request): ?string
    {
        foreach ($this->extractors as $extractor) {
            if (null !== $key = $extractor->extractKey($request)) {
                return $key;
            }
        }

        return null;
    }
}
