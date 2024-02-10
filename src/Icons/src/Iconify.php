<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\Icons\Exception\IconNotFoundException;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class Iconify
{
    public function __construct(private ?HttpClientInterface $httpClient = null)
    {
    }

    public function fetchSvg(string $prefix, string $name): string
    {
        $content = $this->http()
            ->request('GET', sprintf('https://api.iconify.design/%s/%s.svg', $prefix, $name))
            ->getContent()
        ;

        if (!str_starts_with($content, '<svg')) {
            throw new IconNotFoundException(sprintf('The icon "%s:%s" does not exist on iconify.design.', $prefix, $name));
        }

        return $content;
    }

    private function http(): HttpClientInterface
    {
        return $this->httpClient ?? throw new \LogicException('You must install "symfony/http-client" to import icons. Try running "composer require symfony/http-client".');
    }
}
