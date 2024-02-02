<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class UXIconsExtension extends ConfigurableExtension implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('ux_icons');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('icon_dir')
                    ->info('The local directory where icons are stored.')
                    ->defaultValue('%kernel.project_dir%/templates/icons')
                ->end()
                ->scalarNode('twig_component_name')
                    ->info('The name of the Twig component to use for rendering icons.')
                    ->defaultValue('UX:Icon')
                ->end()
                ->variableNode('default_icon_attributes')
                    ->info('Default attributes to add to all icons.')
                    ->defaultValue(['fill' => 'currentColor'])
                ->end()
            ->end()
        ;

        return $builder;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void // @phpstan-ignore-line
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        $container->getDefinition('.ux_icons.local_svg_icon_registry')
            ->setArguments([
                $mergedConfig['icon_dir'],
            ])
        ;

        $container->getDefinition('.ux_icons.icon_renderer')
            ->setArgument(1, $mergedConfig['default_icon_attributes'])
        ;

        $container->getDefinition('.ux_icons.twig_component.icon')
            ->addTag('twig.component', [
                'key' => $mergedConfig['twig_component_name'],
                'template' => '@UXIcons/Icon.html.twig',
            ])
        ;
    }
}
