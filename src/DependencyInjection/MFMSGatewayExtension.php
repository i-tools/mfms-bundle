<?php
/**
 * Created by PhpStorm.
 * User: itools
 * Date: 03.04.19
 * Time: 13:16
 */

declare(strict_types=1);

namespace itools\MFMSGatewayBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class MFMSGatewayExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadConfigurationToContainer($configs, $container);
        $this->loadServicesToContainer($container);
    }

    /**
     * Регистрирует параметры конфигов бандла.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    protected function loadConfigurationToContainer(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);
        foreach ($config as $key => $value) {
            $container->setParameter('mfmsgateway.' . $key, $value);
        }
    }

    protected function loadServicesToContainer(ContainerBuilder $container): void
    {
        $configDir = dirname(__DIR__) . '/Resources/config';
        $loader = new YamlFileLoader($container, new FileLocator($configDir));

        $loader->load('services.yaml');
    }


    public function getAlias()
    {
        return 'mfmsgateway';
    }
}