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
use Symfony\UX\Icons\Svg\Icon;

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

    public function get(string $name): Icon
    {
        if (!Icon::isValidName($name)) {
            throw new IconNotFoundException(sprintf('The icon name "%s" is not valid.', $name));
        }

        if (!file_exists($filename = sprintf('%s/%s.svg', $this->iconDir, str_replace(':', '/', $name)))) {
            throw new IconNotFoundException(sprintf('The icon "%s" (%s) does not exist.', $name, $filename));
        }

        $svg = file_get_contents($filename) ?: throw new \RuntimeException(sprintf('The icon file "%s" could not be read.', $filename));

        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;

        try {
            $doc->loadXML($svg);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf('The icon file "%s" does not contain a valid SVG.', $filename), previous: $e);
        }

        $svgElements = $doc->getElementsByTagName('svg');

        if (0 === $svgElements->length) {
            throw new \RuntimeException(sprintf('The icon file "%s" does not contain a valid SVG.', $filename));
        }

        if (1 !== $svgElements->length) {
            throw new \RuntimeException(sprintf('The icon file "%s" contains more than one SVG.', $filename));
        }

        $svgElement = $svgElements->item(0) ?? throw new \RuntimeException(sprintf('The icon file "%s" does not contain a valid SVG.', $filename));

        $innerSvg = '';

        foreach ($svgElement->childNodes as $child) {
            $innerSvg .= $doc->saveHTML($child);
        }

        if (!$innerSvg) {
            throw new \RuntimeException(sprintf('The icon file "%s" contains an empty SVG.', $filename));
        }

        // @todo: save all attributes in the local object ?
        // allow us to defer the decision of which attributes to keep or not

        $allAttributes = array_map(fn (\DOMAttr $a) => $a->value, [...$svgElement->attributes]);
        $attributes = [];

        if (isset($allAttributes['viewBox'])) {
            $attributes['viewBox'] = $allAttributes['viewBox'];
        }

        return new Icon($innerSvg, $attributes);
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
