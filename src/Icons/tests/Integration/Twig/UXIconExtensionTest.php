<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Tests\Integration\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Environment;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UXIconExtensionTest extends KernelTestCase
{
    public function testRenderIcons(): void
    {
        $output = self::getContainer()->get(Environment::class)->createTemplate(<<<TWIG
            <ul class="svg">
                <li id="first">{{ ux_icon('user', {class: 'h-6 w-6'}) }}</li>
                <li id="second">{{ ux_icon('user') }}</li>
                <li id="third">{{ ux_icon('sub:check') }}</li>
                <li id="forth">{{ ux_icon('sub/check') }}</li>
            </ul>
            TWIG
        )->render();

        $crawler = new Crawler($output);

        $this->assertCount(4, $crawler->filter('.svg svg path'));
        $this->assertCount(1, $crawler->filter('.svg svg.h-6.w-6 path'));
        $this->assertCount(4, $crawler->filter('.svg svg[fill="currentColor"]'));
        $this->assertCount(4, $crawler->filter('.svg svg[viewBox="0 0 24 24"]'));
    }
}
