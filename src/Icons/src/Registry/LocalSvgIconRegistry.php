<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Registry;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\IconRegistryInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class LocalSvgIconRegistry implements IconRegistryInterface
{
    public function __construct(private string $iconDir)
    {
    }

    public function get(string $name): array
    {
        if (!file_exists($filename = sprintf('%s/%s.svg', $this->iconDir, str_replace(':', '/', $name)))) {
            throw new IconNotFoundException(sprintf('The icon "%s" (%s) does not exist.', $name, $filename));
        }

        $svg = file_get_contents($filename) ?: throw new \RuntimeException(sprintf('The icon file "%s" could not be read.', $filename));
        $crawler = (new Crawler($svg))->filter('svg');
        $node = $crawler->getNode(0) ?? throw new \RuntimeException(sprintf('The icon file "%s" is not a valid SVG.', $filename));
        $attributes = [];

        if ($viewBox = $node->attributes?->getNamedItem('viewbox')?->nodeValue) {
            $attributes['viewBox'] = $viewBox;
        }

        return [$crawler->html(), $attributes];
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->finder()->sortByName() as $file) {
            yield str_replace(['.svg', '/'], ['', ':'], $file->getRelativePathname());
        }
    }

    public function count(): int
    {
        return $this->finder()->count();
    }

    private function finder(): Finder
    {
        return Finder::create()->in($this->iconDir)->files()->name('*.svg');
    }
}
