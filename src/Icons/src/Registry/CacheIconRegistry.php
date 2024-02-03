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

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\IconRegistryInterface;
use Symfony\UX\Icons\Svg\Icon;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class CacheIconRegistry implements IconRegistryInterface, CacheWarmerInterface
{
    public function __construct(private IconRegistryInterface $inner, private CacheInterface $cache)
    {
    }

    public function get(string $name): Icon
    {
        return $this->fetchIcon($name);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->inner;
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        foreach ($this as $name) {
            $this->fetchIcon($name, refresh: true);
        }

        return [];
    }

    private function fetchIcon(string $name, bool $refresh = false): Icon
    {
        if (!Icon::isValidName($name)) {
            throw new IconNotFoundException(sprintf('The icon name "%s" is not valid.', $name));
        }

        return $this->cache->get(
            sprintf('ux-icon-%s', Icon::nameToId($name)),
            fn () => $this->inner->get($name),
            beta: $refresh ? \INF : null,
        );
    }
}
