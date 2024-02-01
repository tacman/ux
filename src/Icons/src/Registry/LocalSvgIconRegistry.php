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
        $doc = new \DOMDocument();
        $doc->loadXML($svg);
        $svgTag = $doc->firstElementChild;
        $html = '';

        foreach ($svgTag->childNodes as $child) {
            $html .= $doc->saveHTML($child);
        }

        $allAttributes = array_map(fn (\DOMAttr $a) => $a->value, [...$svgTag->attributes]);
        $attributes = [];

        if (isset($allAttributes['viewBox'])) {
            $attributes['viewBox'] = $allAttributes['viewBox'];
        }

        return [$html, $attributes];
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
