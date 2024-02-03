<?php

namespace Symfony\UX\Icons\Twig;

use Symfony\UX\Icons\IconRenderer;
use Symfony\UX\TwigComponent\Event\PreCreateForRenderEvent;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class UXIconComponentListener
{
    public function __construct(
        private IconRenderer $iconRenderer,
        private string $componentName,
    ) {
    }

    public function onPreCreateForRender(PreCreateForRenderEvent $event): void
    {
        if ($this->componentName !== $event->getName()) {
            return;
        }

        $attributes = $event->getInputProps();
        $name = (string) $attributes['name'];
        unset($attributes['name']);

        $svg = $this->iconRenderer->renderIcon($name, $attributes);
        $event->setRenderedString($svg);
        $event->stopPropagation();
    }
}
