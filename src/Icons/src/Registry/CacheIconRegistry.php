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
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\IconRegistryInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class CacheIconRegistry implements IconRegistryInterface, CacheWarmerInterface
{
    /**
     * @param IconRegistryInterface[] $registries
     */
    public function __construct(private \Traversable $registries, private CacheInterface $cache)
    {
    }

    public function get(string $name, bool $refresh = false): array
    {
        return $this->cache->get(
            sprintf('ux-icon-%s', str_replace([':', '/'], ['-', '-'], $name)),
            function (ItemInterface $item) use ($name) {
                if ($this->cache instanceof TagAwareCacheInterface) {
                    $item->tag('ux-icon');
                }

                foreach ($this->registries as $registry) {
                    try {
                        return $registry->get($name);
                    } catch (IconNotFoundException) {
                        // ignore
                    }
                }

                throw new IconNotFoundException(sprintf('The icon "%s" does not exist.', $name));
            },
            beta: $refresh ? \INF : null,
        );
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->registries as $registry) {
            yield from $registry;
        }
    }

    public function count(): int
    {
        $count = 0;

        foreach ($this->registries as $registry) {
            $count += \count($registry);
        }

        return $count;
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        foreach ($this as $name) {
            $this->get($name, refresh: true);
        }

        return [];
    }
}
