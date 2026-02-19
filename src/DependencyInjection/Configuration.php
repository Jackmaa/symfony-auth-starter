<?php

declare(strict_types=1);

namespace Valentin\AuthStarter\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Extension to load and configure the AuthStarter bundle.
 */
class AuthStarterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set configuration as parameters
        $container->setParameter('auth_starter.redirect_after_login', $config['redirect_after_login']);
        $container->setParameter('auth_starter.enable_google', $config['enable_google']);
        $container->setParameter('auth_starter.auto_verify_oauth_users', $config['auto_verify_oauth_users']);
        $container->setParameter('auth_starter.from_email', $config['from_email']);
        $container->setParameter('auth_starter.from_name', $config['from_name']);

        // Load services
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');
    }

    public function getAlias(): string
    {
        return 'auth_starter';
    }
}