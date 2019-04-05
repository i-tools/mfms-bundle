<?php
/**
 * Created by PhpStorm.
 * User: itools
 * Date: 03.04.19
 * Time: 16:11
 */

namespace itools\MFMSGatewayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Класс с описанием настроек бандла.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     *
     * @psalm-suppress UndefinedMethod
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mfmsgateway');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('url')->defaultValue('')->end()
            ->scalarNode('login')->defaultValue('')->end()
            ->scalarNode('password')->defaultValue('')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
