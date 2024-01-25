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

use Symfony\UX\Icons\Exception\IconNotFoundException;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends \IteratorAggregate<string>
 *
 * @internal
 */
interface IconRegistryInterface extends \IteratorAggregate, \Countable
{
    /**
     * @return array{0: string, 1: array<string, string|bool>}
     *
     * @throws IconNotFoundException
     */
    public function get(string $name): array;
}
